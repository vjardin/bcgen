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

#ifndef ZEND_ACCELERATOR_H
#define ZEND_ACCELERATOR_H

#ifdef HAVE_CONFIG_H
# include <config.h>
#endif

#define ACCELERATOR_PRODUCT_NAME	"Zend BCgen"
/* 2 - added Profiler support, on 20010712 */
/* 3 - added support for Optimizer's encoded-only-files mode */
/* 4 - works with the new Optimizer, that supports the file format with licenses */
/* 5 - API 4 didn't really work with the license-enabled file format.  v5 does. */
/* 6 - Monitor was removed from ZendPlatform.so, to a module of its own */
/* 7 - Optimizer was embedded into Accelerator */
/* 8 - Standalone Open Source Zend OPcache */
#define ACCELERATOR_API_NO 8

#if ZEND_WIN32
# include "zend_config.w32.h"
#else
#include "zend_config.h"
# include <sys/time.h>
# include <sys/resource.h>
#endif

#if HAVE_UNISTD_H
# include "unistd.h"
#endif

#include "zend_extensions.h"
#include "zend_compile.h"

#include "Optimizer/zend_optimizer.h"
#include "zend_accelerator_debug.h"

#ifndef PHPAPI
# ifdef ZEND_WIN32
#  define PHPAPI __declspec(dllimport)
# else
#  define PHPAPI
# endif
#endif

#ifndef ZEND_EXT_API
# if WIN32|WINNT
#  define ZEND_EXT_API __declspec(dllexport)
# elif defined(__GNUC__) && __GNUC__ >= 4
#  define ZEND_EXT_API __attribute__ ((visibility("default")))
# else
#  define ZEND_EXT_API
# endif
#endif

#ifdef ZEND_WIN32
# ifndef MAXPATHLEN
#  include "win32/ioutil.h"
#  define MAXPATHLEN PHP_WIN32_IOUTIL_MAXPATHLEN
# endif
# include <direct.h>
#else
# ifndef MAXPATHLEN
#  define MAXPATHLEN     4096
# endif
# include <sys/param.h>
#endif

typedef struct _zend_persistent_script {
	zend_script    script;
	zend_long      compiler_halt_offset;   /* position of __HALT_COMPILER or -1 */
	int            ping_auto_globals_mask; /* which autoglobals are used by the script */

	void          *mem;                    /* shared memory area used by script structures */
	size_t         size;                   /* size of used shared memory */
	void          *arena_mem;              /* part that should be copied into process */
	size_t         arena_size;

	/* All entries that shouldn't be counted in the ADLER32
	 * checksum must be declared in this struct
	 */
	struct zend_persistent_script_dynamic_members {
		unsigned int checksum;
	} dynamic_members;
} zend_persistent_script;

typedef struct _zend_accel_directives {
	zend_long      consistency_checks;
	zend_bool      ignore_dups;
	zend_bool      save_comments;
	char          *error_log;
	zend_long      log_verbosity_level;

	zend_long      optimization_level;
	zend_long      opt_debug_level;
	zend_bool      file_consistency_checks;
#ifdef HAVE_HUGE_CODE_PAGES
	zend_bool      huge_code_pages;
#endif
} zend_accel_directives;

typedef struct _zend_accel_globals {
    /* copy of CG(function_table) used for compilation scripts into cache */
    /* initially it contains only internal functions */
	HashTable               function_table;
	int                     internal_functions_count;
	zend_bool               enabled;
	HashTable               bind_hash; /* prototype and zval lookup table */
	zend_accel_directives   accel_directives;
	int                     auto_globals_mask;
	char                    system_id[32];
    char                   *outfilename;
	HashTable               xlat_table;
	/* preallocated memory block to save current script */
	void                   *mem;
	void                   *arena_mem;
	zend_persistent_script *current_persistent_script;
} zend_accel_globals;

typedef struct _zend_accel_shared_globals {
	/* Interned Strings Support */
	char           *interned_strings_start;
	char           *interned_strings_top;
	char           *interned_strings_end;
	char           *interned_strings_saved_top;
	HashTable       interned_strings;
	/* uninitialized HashTable Support */
	uint32_t uninitialized_bucket[-HT_MIN_MASK];
} zend_accel_shared_globals;

extern zend_bool accel_startup_ok;

extern zend_accel_shared_globals *accel_shared_globals;
#define ZCSG(element)   (accel_shared_globals->element)

#ifdef ZTS
# define ZCG(v)	ZEND_TSRMG(accel_globals_id, zend_accel_globals *, v)
extern int accel_globals_id;
# ifdef COMPILE_DL_OPCACHE
ZEND_TSRMLS_CACHE_EXTERN()
# endif
#else
# define ZCG(v) (accel_globals.v)
extern zend_accel_globals accel_globals;
#endif

extern char *zps_api_failure_reason;

void accel_shutdown(void);
int  accel_post_deactivate(void);

zend_op_array *persistent_load_file(zend_file_handle *file_handle, int type);
void persistent_compile_file(zend_file_handle *file_handle, int type);

#define IS_ACCEL_INTERNED(str) \
	((char*)(str) >= ZCSG(interned_strings_start) && (char*)(str) < ZCSG(interned_strings_end))

#endif /* ZEND_ACCELERATOR_H */
