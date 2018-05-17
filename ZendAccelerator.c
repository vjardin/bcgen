/*
   +----------------------------------------------------------------------+
   | Zend BCgen                                                         |
   +----------------------------------------------------------------------+
   | Copyright (c) 1998-2018 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.01 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.php.net/license/3_01.txt                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Andi Gutmans <andi@zend.com>                                |
   |          Zeev Suraski <zeev@zend.com>                                |
   |          Stanislav Malyshev <stas@zend.com>                          |
   |          Dmitry Stogov <dmitry@zend.com>                             |
   +----------------------------------------------------------------------+
*/

#include "main/php.h"
#include "main/php_globals.h"
#include "zend.h"
#include "zend_extensions.h"
#include "zend_compile.h"
#include "ZendAccelerator.h"
#include "zend_persist.h"
#include "zend_shared_alloc.h"
#include "zend_accelerator_module.h"
#include "zend_list.h"
#include "zend_execute.h"
#include "main/SAPI.h"
#include "main/php_streams.h"
#include "main/php_open_temporary_file.h"
#include "zend_API.h"
#include "zend_ini.h"
#include "zend_virtual_cwd.h"
#include "zend_accelerator_util_funcs.h"
#include "ext/standard/md5.h"

#include "zend_file_cache.h"

#ifndef ZEND_WIN32
#include  <netdb.h>
#endif

#ifdef ZEND_WIN32
typedef int uid_t;
typedef int gid_t;
#include <io.h>
#endif

#ifndef ZEND_WIN32
# include <sys/time.h>
#else
# include <process.h>
#endif

#ifdef HAVE_UNISTD_H
# include <unistd.h>
#endif
#include <fcntl.h>
#include <signal.h>
#include <time.h>

#ifndef ZEND_WIN32
# include <sys/types.h>
# include <sys/ipc.h>
#endif

#include <sys/stat.h>
#include <errno.h>

ZEND_EXTENSION();

#ifndef ZTS
zend_accel_globals accel_globals;
#else
int accel_globals_id;
#if defined(COMPILE_DL_OPCACHE)
ZEND_TSRMLS_CACHE_DEFINE()
#endif
#endif

/* true globals, no need for thread safety */
zend_bool accel_startup_ok = 0;
static char *zps_failure_reason = NULL;
char *zps_api_failure_reason = NULL;

static zend_op_array *(*accelerator_orig_compile_file)(zend_file_handle *file_handle, int type);

static void accel_gen_system_id(void);

static void accel_gen_system_id(void);
static int accel_post_startup(void);

static inline int is_stream_path(const char *filename)
{
	const char *p;

	for (p = filename;
	     (*p >= 'a' && *p <= 'z') ||
	     (*p >= 'A' && *p <= 'Z') ||
	     (*p >= '0' && *p <= '9') ||
	     *p == '+' || *p == '-' || *p == '.';
	     p++);
	return ((p != filename) && (p[0] == ':') && (p[1] == '/') && (p[2] == '/'));
}

static inline int is_cacheable_stream_path(const char *filename)
{
	return memcmp(filename, "file://", sizeof("file://") - 1) == 0 ||
	       memcmp(filename, "phar://", sizeof("phar://") - 1) == 0;
}

static zend_persistent_script *store_script_in_file_cache(zend_persistent_script *new_persistent_script)
{
	uint32_t memory_used;

	zend_shared_alloc_init_xlat_table();

	/* Calculate the required memory size */
	memory_used = zend_accel_script_persist_calc(new_persistent_script, NULL, 0);

	/* Allocate memory block */
#ifdef __SSE2__
	/* Align to 64-byte boundary */
	ZCG(mem) = zend_arena_alloc(&CG(arena), memory_used + 64);
	ZCG(mem) = (void*)(((zend_uintptr_t)ZCG(mem) + 63L) & ~63L);
#else
	ZCG(mem) = zend_arena_alloc(&CG(arena), memory_used);
#endif

	/* Copy into memory block */
	new_persistent_script = zend_accel_script_persist(new_persistent_script, NULL, 0);

	zend_shared_alloc_destroy_xlat_table();

	/* Consistency check */
	if ((char*)new_persistent_script->mem + new_persistent_script->size != (char*)ZCG(mem)) {
		zend_accel_error(
			((char*)new_persistent_script->mem + new_persistent_script->size < (char*)ZCG(mem)) ? ACCEL_LOG_ERROR : ACCEL_LOG_WARNING,
			"Internal error: wrong size calculation: %s start=" ZEND_ADDR_FMT ", end=" ZEND_ADDR_FMT ", real=" ZEND_ADDR_FMT "\n",
			ZSTR_VAL(new_persistent_script->script.filename),
			(size_t)new_persistent_script->mem,
			(size_t)((char *)new_persistent_script->mem + new_persistent_script->size),
			(size_t)ZCG(mem));
	}

	new_persistent_script->dynamic_members.checksum = zend_accel_script_checksum(new_persistent_script);

	zend_file_cache_script_store(new_persistent_script);

	return new_persistent_script;
}

static zend_persistent_script *cache_script_in_file_cache(zend_persistent_script *new_persistent_script)
{


    if (!zend_optimize_script(&new_persistent_script->script, ZCG(accel_directives).optimization_level, ZCG(accel_directives).opt_debug_level)) {
        return new_persistent_script;
    }


    return store_script_in_file_cache(new_persistent_script);
}

static const struct jit_auto_global_info
{
    const char *name;
    size_t len;
} jit_auto_globals_info[] = {
    { "_SERVER",  sizeof("_SERVER")-1},
    { "_ENV",     sizeof("_ENV")-1},
    { "_REQUEST", sizeof("_REQUEST")-1},
    { "GLOBALS",  sizeof("GLOBALS")-1},
};

static zend_string *jit_auto_globals_str[4];

static int zend_accel_get_auto_globals(void)
{
	int i, ag_size = (sizeof(jit_auto_globals_info) / sizeof(jit_auto_globals_info[0]));
	int n = 1;
	int mask = 0;

	for (i = 0; i < ag_size ; i++) {
		if (zend_hash_exists(&EG(symbol_table), jit_auto_globals_str[i])) {
			mask |= n;
		}
		n += n;
	}
	return mask;
}

static int zend_accel_get_auto_globals_no_jit(void)
{
	if (zend_hash_exists(&EG(symbol_table), jit_auto_globals_str[3])) {
		return 8;
	}
	return 0;
}

static void zend_accel_set_auto_globals(int mask)
{
	int i, ag_size = (sizeof(jit_auto_globals_info) / sizeof(jit_auto_globals_info[0]));
	int n = 1;

	for (i = 0; i < ag_size ; i++) {
		if ((mask & n) && !(ZCG(auto_globals_mask) & n)) {
			ZCG(auto_globals_mask) |= n;
			zend_is_auto_global(jit_auto_globals_str[i]);
		}
		n += n;
	}
}

static void zend_accel_init_auto_globals(void)
{
	int i, ag_size = (sizeof(jit_auto_globals_info) / sizeof(jit_auto_globals_info[0]));

	for (i = 0; i < ag_size ; i++) {
		jit_auto_globals_str[i] = zend_string_init(jit_auto_globals_info[i].name, jit_auto_globals_info[i].len, 1);
		zend_string_hash_val(jit_auto_globals_str[i]);
	}
}

static zend_persistent_script *bcgen_compile_file(zend_file_handle *file_handle, int type)
{
    zend_persistent_script *new_persistent_script;
    zend_op_array *orig_active_op_array;
    HashTable *orig_function_table, *orig_class_table;
    zval orig_user_error_handler;
    zend_op_array *op_array;
    int do_bailout = 0;
    uint32_t orig_compiler_options = 0;

    /* Try to open file */
    if (file_handle->type == ZEND_HANDLE_FILENAME) {
        if (zend_stream_open_function(file_handle->filename, file_handle) != SUCCESS) {
            if (type == ZEND_REQUIRE) {
                zend_message_dispatcher(ZMSG_FAILED_REQUIRE_FOPEN, file_handle->filename);
                zend_bailout();
            } else {
                zend_message_dispatcher(ZMSG_FAILED_INCLUDE_FOPEN, file_handle->filename);
            }
            return NULL;
        }
    }

    new_persistent_script = create_persistent_script();

    /* Save the original values for the op_array, function table and class table */
    orig_active_op_array = CG(active_op_array);
    orig_function_table = CG(function_table);
    orig_class_table = CG(class_table);
    ZVAL_COPY_VALUE(&orig_user_error_handler, &EG(user_error_handler));

    /* Override them with ours */
    CG(function_table) = &ZCG(function_table);
    EG(class_table) = CG(class_table) = &new_persistent_script->script.class_table;
    ZVAL_UNDEF(&EG(user_error_handler));

    zend_try {
        orig_compiler_options = CG(compiler_options);
        CG(compiler_options) |= ZEND_COMPILE_HANDLE_OP_ARRAY;
        CG(compiler_options) |= ZEND_COMPILE_IGNORE_INTERNAL_CLASSES;
        CG(compiler_options) |= ZEND_COMPILE_DELAYED_BINDING;
        CG(compiler_options) |= ZEND_COMPILE_NO_CONSTANT_SUBSTITUTION;
        op_array = accelerator_orig_compile_file(file_handle, type);
        CG(compiler_options) = orig_compiler_options;
    } zend_catch {
        op_array = NULL;
        do_bailout = 1;
        CG(compiler_options) = orig_compiler_options;
    } zend_end_try();

    /* Restore originals */
    CG(active_op_array) = orig_active_op_array;
    CG(function_table) = orig_function_table;
    EG(class_table) = CG(class_table) = orig_class_table;
    EG(user_error_handler) = orig_user_error_handler;

    if (!op_array) {
        /* compilation failed */
        free_persistent_script(new_persistent_script, 1);
        zend_accel_free_user_functions(&ZCG(function_table));
        if (do_bailout) {
            zend_bailout();
        }
        return NULL;
    }

    /* Build the persistent_script structure.
       Here we aren't sure we would store it, but we will need it
       further anyway.
    */
    zend_accel_move_user_functions(&ZCG(function_table), &new_persistent_script->script.function_table);
    new_persistent_script->script.main_op_array = *op_array;

    efree(op_array); /* we have valid persistent_script, so it's safe to free op_array */

    /* Fill in the ping_auto_globals_mask for the new script. If jit for auto globals is enabled we
       will have to ping the used auto global variables before execution */
    if (PG(auto_globals_jit)) {
        new_persistent_script->ping_auto_globals_mask = zend_accel_get_auto_globals();
    } else {
        new_persistent_script->ping_auto_globals_mask = zend_accel_get_auto_globals_no_jit();
    }

    if (file_handle->opened_path) {
        new_persistent_script->script.filename = zend_string_copy(file_handle->opened_path);
    } else {
        new_persistent_script->script.filename = zend_string_init(file_handle->filename, strlen(file_handle->filename), 0);
    }
    zend_string_hash_val(new_persistent_script->script.filename);

    /* Now persistent_script structure is ready in process memory */
    return new_persistent_script;
}

zend_op_array *file_cache_compile_file(zend_file_handle *file_handle, int type)
{
    zend_persistent_script *persistent_script;

    if (is_stream_path(file_handle->filename) &&
        !is_cacheable_stream_path(file_handle->filename)) {
        return accelerator_orig_compile_file(file_handle, type);
    }

    if (!file_handle->opened_path) {
        if (file_handle->type == ZEND_HANDLE_FILENAME &&
            zend_stream_open_function(file_handle->filename, file_handle) == FAILURE) {
            if (type == ZEND_REQUIRE) {
                zend_message_dispatcher(ZMSG_FAILED_REQUIRE_FOPEN, file_handle->filename);
                zend_bailout();
            } else {
                zend_message_dispatcher(ZMSG_FAILED_INCLUDE_FOPEN, file_handle->filename);
            }
            return NULL;
        }
    }

    HANDLE_BLOCK_INTERRUPTIONS();
    persistent_script = zend_file_cache_script_load(file_handle);
    HANDLE_UNBLOCK_INTERRUPTIONS();
    if (persistent_script) {
        zend_file_handle_dtor(file_handle);

        if (persistent_script->ping_auto_globals_mask) {
            zend_accel_set_auto_globals(persistent_script->ping_auto_globals_mask);
        }

        return zend_accel_load_script(persistent_script);
    }

    return accelerator_orig_compile_file(file_handle, type);
}

/* zend_compile() replacement */
zend_op_array *persistent_load_file(zend_file_handle *file_handle, int type)
{
    if (!file_handle->filename || !ZCG(enabled) || !accel_startup_ok) {
        /* The Accelerator is disabled, act as if without the Accelerator */
        return accelerator_orig_compile_file(file_handle, type);
    } 
    
    return file_cache_compile_file(file_handle, type);
}

void persistent_compile_file(zend_file_handle *file_handle, int type)
{
    zend_persistent_script *persistent_script;

    persistent_script = bcgen_compile_file(file_handle, type);

    if (persistent_script) {
        persistent_script = cache_script_in_file_cache(persistent_script);
        // TODO: free_persistent_script() produces zend_mm_heap corrupted ?!
        // free_persistent_script(persistent_script, 1);
        // zend_accel_free_user_functions(&ZCG(function_table));        
    }
}

static void accel_activate(void)
{
    if (!ZCG(enabled) || !accel_startup_ok) {
        return;
    }

    if (!ZCG(function_table).nTableSize) {
        zend_hash_init(&ZCG(function_table), zend_hash_num_elements(CG(function_table)), NULL, ZEND_FUNCTION_DTOR, 1);
        zend_accel_copy_internal_functions();
    }

    /* PHP-5.4 and above return "double", but we use 1 sec precision */
    ZCG(auto_globals_mask) = 0;

    /* check if ZCG(function_table) wasn't somehow polluted on the way */
    if (ZCG(internal_functions_count) != (zend_long)zend_hash_num_elements(&ZCG(function_table))) {
        zend_accel_error(ACCEL_LOG_WARNING, "Internal functions count changed - was %d, now %d", ZCG(internal_functions_count), zend_hash_num_elements(&ZCG(function_table)));
    }
}

int accel_post_deactivate(void)
{
    if (!ZCG(enabled) || !accel_startup_ok) {
        return SUCCESS;
    }

    return SUCCESS;
}

static void accel_deactivate(void)
{
    /* ensure that we restore function_table and class_table
     * In general, they're restored by persistent_compile_file(), but in case
     * the script is aborted abnormally, they may become messed up.
     */

    if (!ZCG(enabled) || !accel_startup_ok) {
        return;
    }
}

static int accelerator_remove_cb(zend_extension *element1, zend_extension *element2)
{
    (void)element2; /* keep the compiler happy */

    if (!strcmp(element1->name, ACCELERATOR_PRODUCT_NAME )) {
        element1->startup = NULL;
#if 0
        /* We have to call shutdown callback it to free TS resources */
        element1->shutdown = NULL;
#endif
        element1->activate = NULL;
        element1->deactivate = NULL;
        element1->op_array_handler = NULL;

#ifdef __DEBUG_MESSAGES__
        fprintf(stderr, ACCELERATOR_PRODUCT_NAME " is disabled: %s\n", (zps_failure_reason ? zps_failure_reason : "unknown error"));
        fflush(stderr);
#endif
    }

    return 0;
}

static void zps_startup_failure(char *reason, char *api_reason, int (*cb)(zend_extension *, zend_extension *))
{
    accel_startup_ok = 0;
    zps_failure_reason = reason;
    zps_api_failure_reason = api_reason?api_reason:reason;
    zend_llist_del_element(&zend_extensions, NULL, (int (*)(void *, void *))cb);
}

static inline int accel_find_sapi(void)
{
    static const char *supported_sapis[] = {
        "apache",
        "fastcgi",
        "cli-server",
        "cgi-fcgi",
        "fpm-fcgi",
        "apache2handler",
        "litespeed",
        "uwsgi",
        "cli",
        "phpdbg",
        NULL
    };
    const char **sapi_name;

    if (sapi_module.name) {
        for (sapi_name = supported_sapis; *sapi_name; sapi_name++) {
            if (strcmp(sapi_module.name, *sapi_name) == 0) {
                return SUCCESS;
            }
        }
    }

    return FAILURE;
}

static void accel_globals_ctor(zend_accel_globals *accel_globals)
{
#if defined(COMPILE_DL_OPCACHE) && defined(ZTS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif
	memset(accel_globals, 0, sizeof(zend_accel_globals));

	/* TODO refactor to init this just once. */
	accel_gen_system_id();
}

static void accel_globals_internal_func_dtor(zval *zv)
{
	free(Z_PTR_P(zv));
}

static void accel_globals_dtor(zend_accel_globals *accel_globals)
{
	if (accel_globals->function_table.nTableSize) {
		accel_globals->function_table.pDestructor = accel_globals_internal_func_dtor;
		zend_hash_destroy(&accel_globals->function_table);
	}
}

#define ZEND_BIN_ID "BIN_" ZEND_TOSTR(SIZEOF_CHAR) ZEND_TOSTR(SIZEOF_INT) ZEND_TOSTR(SIZEOF_LONG) ZEND_TOSTR(SIZEOF_SIZE_T) ZEND_TOSTR(SIZEOF_ZEND_LONG) ZEND_TOSTR(ZEND_MM_ALIGNMENT)

static void accel_gen_system_id(void)
{
    PHP_MD5_CTX context;
    unsigned char digest[16], c;
    char *md5str = ZCG(system_id);
    int i;

    PHP_MD5Init(&context);
    PHP_MD5Update(&context, PHP_VERSION, sizeof(PHP_VERSION)-1);
    PHP_MD5Update(&context, ZEND_EXTENSION_BUILD_ID, sizeof(ZEND_EXTENSION_BUILD_ID)-1);
    PHP_MD5Update(&context, ZEND_BIN_ID, sizeof(ZEND_BIN_ID)-1);
    PHP_MD5Final(digest, &context);
    for (i = 0; i < 16; i++) {
        c = digest[i] >> 4;
        c = (c <= 9) ? c + '0' : c - 10 + 'a';
        md5str[i * 2] = c;
        c = digest[i] &  0x0f;
        c = (c <= 9) ? c + '0' : c - 10 + 'a';
        md5str[(i * 2) + 1] = c;
    }
}

#ifdef HAVE_HUGE_CODE_PAGES
# ifndef _WIN32
#  include <sys/mman.h>
#  ifndef MAP_ANON
#   ifdef MAP_ANONYMOUS
#    define MAP_ANON MAP_ANONYMOUS
#   endif
#  endif
#  ifndef MAP_FAILED
#   define MAP_FAILED ((void*)-1)
#  endif
# endif

# if defined(MAP_HUGETLB) || defined(MADV_HUGEPAGE)
static int accel_remap_huge_pages(void *start, size_t size, const char *name, size_t offset)
{
	void *ret = MAP_FAILED;
	void *mem;

	mem = mmap(NULL, size,
		PROT_READ | PROT_WRITE,
		MAP_PRIVATE | MAP_ANONYMOUS,
		-1, 0);
	if (mem == MAP_FAILED) {
		zend_error(E_WARNING,
			ACCELERATOR_PRODUCT_NAME " huge_code_pages: mmap failed: %s (%d)",
			strerror(errno), errno);
		return -1;
	}
	memcpy(mem, start, size);

#  ifdef MAP_HUGETLB
	ret = mmap(start, size,
		PROT_READ | PROT_WRITE | PROT_EXEC,
		MAP_PRIVATE | MAP_ANONYMOUS | MAP_FIXED | MAP_HUGETLB,
		-1, 0);
#  endif
	if (ret == MAP_FAILED) {
		ret = mmap(start, size,
			PROT_READ | PROT_WRITE | PROT_EXEC,
			MAP_PRIVATE | MAP_ANONYMOUS | MAP_FIXED,
			-1, 0);
		/* this should never happen? */
		ZEND_ASSERT(ret != MAP_FAILED);
#  ifdef MADV_HUGEPAGE
		if (-1 == madvise(start, size, MADV_HUGEPAGE)) {
			memcpy(start, mem, size);
			mprotect(start, size, PROT_READ | PROT_EXEC);
			munmap(mem, size);
			zend_error(E_WARNING,
				ACCELERATOR_PRODUCT_NAME " huge_code_pages: madvise(HUGEPAGE) failed: %s (%d)",
				strerror(errno), errno);
			return -1;
		}
#  else
		memcpy(start, mem, size);
		mprotect(start, size, PROT_READ | PROT_EXEC);
		munmap(mem, size);
		zend_error(E_WARNING,
			ACCELERATOR_PRODUCT_NAME " huge_code_pages: mmap(HUGETLB) failed: %s (%d)",
			strerror(errno), errno);
		return -1;
#  endif
	}

	if (ret == start) {
		memcpy(start, mem, size);
		mprotect(start, size, PROT_READ | PROT_EXEC);
	}
	munmap(mem, size);

	return (ret == start) ? 0 : -1;
}

static void accel_move_code_to_huge_pages(void)
{
	FILE *f;
	long unsigned int huge_page_size = 2 * 1024 * 1024;

	f = fopen("/proc/self/maps", "r");
	if (f) {
		long unsigned int  start, end, offset, inode;
		char perm[5], dev[6], name[MAXPATHLEN];
		int ret;

		ret = fscanf(f, "%lx-%lx %4s %lx %5s %ld %s\n", &start, &end, perm, &offset, dev, &inode, name);
		if (ret == 7 && perm[0] == 'r' && perm[1] == '-' && perm[2] == 'x' && name[0] == '/') {
			long unsigned int  seg_start = ZEND_MM_ALIGNED_SIZE_EX(start, huge_page_size);
			long unsigned int  seg_end = (end & ~(huge_page_size-1L));

			if (seg_end > seg_start) {
				zend_accel_error(ACCEL_LOG_DEBUG, "remap to huge page %lx-%lx %s \n", seg_start, seg_end, name);
				accel_remap_huge_pages((void*)seg_start, seg_end - seg_start, name, offset + seg_start - start);
			}
		}
		fclose(f);
	}
}
# else
static void accel_move_code_to_huge_pages(void)
{
	zend_error(E_WARNING, ACCELERATOR_PRODUCT_NAME ": bcgen.huge_code_pages has no affect as huge page is not supported");
	return;
}
# endif /* defined(MAP_HUGETLB) || defined(MADV_HUGEPAGE) */
#endif /* HAVE_HUGE_CODE_PAGES */

static int accel_startup(zend_extension *extension)
{
#ifdef ZTS
	accel_globals_id = ts_allocate_id(&accel_globals_id, sizeof(zend_accel_globals), (ts_allocate_ctor) accel_globals_ctor, (ts_allocate_dtor) accel_globals_dtor);
#else
	accel_globals_ctor(&accel_globals);
#endif

#ifdef ZEND_WIN32
	_setmaxstdio(2048); /* The default configuration is limited to 512 stdio files */
#endif

	if (start_accel_module() == FAILURE) {
		accel_startup_ok = 0;
		zend_error(E_WARNING, ACCELERATOR_PRODUCT_NAME ": module registration failed!");
		return FAILURE;
	}

	accel_gen_system_id();

#ifdef HAVE_HUGE_CODE_PAGES
	if (ZCG(accel_directives).huge_code_pages &&
	    (strcmp(sapi_module.name, "cli") == 0 ||
	     strcmp(sapi_module.name, "cli-server") == 0 ||
		 strcmp(sapi_module.name, "cgi-fcgi") == 0 ||
		 strcmp(sapi_module.name, "fpm-fcgi") == 0)) {
		accel_move_code_to_huge_pages();
	}
#endif

    /* no supported SAPI found - disable acceleration and stop initialization */
    if (accel_find_sapi() == FAILURE) {
        accel_startup_ok = 0;
        zps_startup_failure("Opcode Caching is only supported in CLI, Apache, FPM, FastCGI and LiteSpeed SAPIs", NULL, accelerator_remove_cb);
        return SUCCESS;
    }

    if (ZCG(enabled) == 0) {
        return SUCCESS ;
    }

/********************************************/
/* End of non-SHM dependent initializations */
/********************************************/

    /* Init auto-global strings */
    zend_accel_init_auto_globals();

    /* Override compiler */
    accelerator_orig_compile_file = zend_compile_file;
    zend_compile_file = persistent_load_file;

    accel_startup_ok = 1;

    zend_optimizer_startup();

    return SUCCESS;
}

static void accel_free_ts_resources()
{
#ifndef ZTS
	accel_globals_dtor(&accel_globals);
#else
	ts_free_id(accel_globals_id);
#endif
}

void accel_shutdown(void)
{
    zend_ini_entry *ini_entry;

    zend_optimizer_shutdown();

    if (!ZCG(enabled) || !accel_startup_ok) {
        accel_free_ts_resources();
        return;
    }

    accel_free_ts_resources();

    zend_compile_file = accelerator_orig_compile_file;
}

ZEND_EXT_API zend_extension zend_extension_entry = {
    ACCELERATOR_PRODUCT_NAME,               /* name */
    PHP_VERSION,                            /* version */
    "Zend Technologies",                    /* author */
    "http://www.zend.com/",                 /* URL */
    "Copyright (c) 1999-2018",              /* copyright */
    accel_startup,                          /* startup */
    NULL,                                   /* shutdown */
    accel_activate,                         /* per-script activation */
    accel_deactivate,                       /* per-script deactivation */
    NULL,                                   /* message handler */
    NULL,                                   /* op_array handler */
    NULL,                                   /* extended statement handler */
    NULL,                                   /* extended fcall begin handler */
    NULL,                                   /* extended fcall end handler */
    NULL,                                   /* op_array ctor */
    NULL,                                   /* op_array dtor */
    STANDARD_ZEND_EXTENSION_PROPERTIES
};
