/*
   +----------------------------------------------------------------------+
   | Zend BCgen                                                       |
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

#include <time.h>

#include "php.h"
#include "ZendAccelerator.h"
#include "zend_API.h"
#include "zend_shared_alloc.h"
#include "php_ini.h"
#include "SAPI.h"
#include "zend_virtual_cwd.h"
#include "ext/standard/info.h"
#include "ext/standard/php_filestat.h"

#define STRING_NOT_NULL(s) (NULL == (s)?"":s)
#define MIN_ACCEL_FILES 200
#define MAX_ACCEL_FILES 1000000
#define TOKENTOSTR(X) #X

ZEND_BEGIN_ARG_INFO_EX(arginfo_bcgen_compile_file, 0, 0, 2)
	ZEND_ARG_INFO(0, infile)
	ZEND_ARG_INFO(0, outfile)
ZEND_END_ARG_INFO()

/* User functions */
static ZEND_FUNCTION(bcgen_compile_file);

static const zend_function_entry accel_functions[] = {
	/* User functions */
	ZEND_FE(bcgen_compile_file,			arginfo_bcgen_compile_file)
	ZEND_FE_END
};

static ZEND_INI_MH(OnEnable)
{
	if (stage == ZEND_INI_STAGE_STARTUP ||
	    stage == ZEND_INI_STAGE_SHUTDOWN ||
	    stage == ZEND_INI_STAGE_DEACTIVATE) {
		return OnUpdateBool(entry, new_value, mh_arg1, mh_arg2, mh_arg3, stage);
	} else {
		/* It may be only temporary disabled */
		zend_bool *p;
#ifndef ZTS
		char *base = (char *) mh_arg2;
#else
		char *base = (char *) ts_resource(*((int *) mh_arg2));
#endif

		p = (zend_bool *) (base+(size_t) mh_arg1);
		if ((ZSTR_LEN(new_value) == 2 && strcasecmp("on", ZSTR_VAL(new_value)) == 0) ||
		    (ZSTR_LEN(new_value) == 3 && strcasecmp("yes", ZSTR_VAL(new_value)) == 0) ||
		    (ZSTR_LEN(new_value) == 4 && strcasecmp("true", ZSTR_VAL(new_value)) == 0) ||
			atoi(ZSTR_VAL(new_value)) != 0) {
			zend_error(E_WARNING, ACCELERATOR_PRODUCT_NAME " can't be temporary enabled (it may be only disabled till the end of request)");
			return FAILURE;
		} else {
			*p = 0;
			return SUCCESS;
		}
	}
}

ZEND_INI_BEGIN()
	STD_PHP_INI_BOOLEAN("bcgen.enable"             , "1", PHP_INI_ALL,    OnEnable,     enabled                             , zend_accel_globals, accel_globals)
    
	STD_PHP_INI_BOOLEAN("bcgen.dups_fix"           , "0", PHP_INI_ALL   , OnUpdateBool, accel_directives.ignore_dups        , zend_accel_globals, accel_globals)

	STD_PHP_INI_ENTRY("bcgen.log_verbosity_level"   , "1"   , PHP_INI_SYSTEM, OnUpdateLong, accel_directives.log_verbosity_level,       zend_accel_globals, accel_globals)

	STD_PHP_INI_ENTRY("bcgen.save_comments"         , "1"  , PHP_INI_SYSTEM, OnUpdateBool,                  accel_directives.save_comments,             zend_accel_globals, accel_globals)

	STD_PHP_INI_ENTRY("bcgen.optimization_level"    , DEFAULT_OPTIMIZATION_LEVEL , PHP_INI_SYSTEM, OnUpdateLong, accel_directives.optimization_level,   zend_accel_globals, accel_globals)
	STD_PHP_INI_ENTRY("bcgen.opt_debug_level"       , "0"      , PHP_INI_SYSTEM, OnUpdateLong,             accel_directives.opt_debug_level,            zend_accel_globals, accel_globals)
	STD_PHP_INI_ENTRY("bcgen.error_log"                , ""    , PHP_INI_SYSTEM, OnUpdateString,	         accel_directives.error_log,                 zend_accel_globals, accel_globals)

	STD_PHP_INI_ENTRY("bcgen.file_consistency_checks" , "1"   , PHP_INI_SYSTEM, OnUpdateBool,	   accel_directives.file_consistency_checks, zend_accel_globals, accel_globals)
#ifdef HAVE_HUGE_CODE_PAGES
	STD_PHP_INI_BOOLEAN("bcgen.huge_code_pages"             , "0"   , PHP_INI_SYSTEM, OnUpdateBool,      accel_directives.huge_code_pages,               zend_accel_globals, accel_globals)
#endif
ZEND_INI_END()

static ZEND_MINIT_FUNCTION(zend_accelerator)
{
	(void)type; /* keep the compiler happy */

	REGISTER_INI_ENTRIES();

	return SUCCESS;
}

static ZEND_MSHUTDOWN_FUNCTION(zend_accelerator)
{
	(void)type; /* keep the compiler happy */

	UNREGISTER_INI_ENTRIES();
	accel_shutdown();
	return SUCCESS;
}

void zend_accel_info(ZEND_MODULE_INFO_FUNC_ARGS)
{
	php_info_print_table_start();

	if (ZCG(enabled) && accel_startup_ok) {
		php_info_print_table_row(2, "BCgen", "Up and Running");
	} else {
		php_info_print_table_row(2, "BCgen", "Disabled");
	}
	if (ZCG(enabled) && accel_startup_ok && ZCG(accel_directives).optimization_level) {
		php_info_print_table_row(2, "Optimization", "Enabled");
	} else {
		php_info_print_table_row(2, "Optimization", "Disabled");
	}
    if (!accel_startup_ok || zps_api_failure_reason) {
        php_info_print_table_row(2, "Startup Failed", zps_api_failure_reason);
    } else {
        php_info_print_table_row(2, "Startup", "OK");
    }
	php_info_print_table_end();
	DISPLAY_INI_ENTRIES();
}

static zend_module_entry accel_module_entry = {
	STANDARD_MODULE_HEADER,
	ACCELERATOR_PRODUCT_NAME,
	accel_functions,
	ZEND_MINIT(zend_accelerator),
	ZEND_MSHUTDOWN(zend_accelerator),
	NULL,
	NULL,
	zend_accel_info,
	PHP_VERSION,
	NO_MODULE_GLOBALS,
	accel_post_deactivate,
	STANDARD_MODULE_PROPERTIES_EX
};

int start_accel_module(void)
{
	return zend_startup_module(&accel_module_entry);
}

static ZEND_FUNCTION(bcgen_compile_file)
{
	char *script_name;
	size_t script_name_len;
	char *outscript_name;
	size_t outscript_name_len;
	zend_file_handle handle;
	zend_execute_data *orig_execute_data = NULL;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "ss", &script_name, &script_name_len, &outscript_name, &outscript_name_len) == FAILURE) {
		return;
	}

	if (!ZCG(enabled) || !accel_startup_ok) {
		zend_error(E_NOTICE, ACCELERATOR_PRODUCT_NAME " seems to be disabled, can't compile file");
		RETURN_FALSE;
	}

    ZCG(outfilename) = emalloc(outscript_name_len + 1);
    memcpy(ZCG(outfilename), outscript_name, outscript_name_len);
    ZCG(outfilename)[outscript_name_len + 1] = '\0';
    
	handle.filename = script_name;
	handle.free_filename = 0;
	handle.opened_path = NULL;
	handle.type = ZEND_HANDLE_FILENAME;

	orig_execute_data = EG(current_execute_data);

	zend_try {
		persistent_compile_file(&handle, ZEND_INCLUDE);
	} zend_catch {
		EG(current_execute_data) = orig_execute_data;
		zend_error(E_WARNING, ACCELERATOR_PRODUCT_NAME " could not compile file %s", handle.filename);
        RETVAL_FALSE;
	} zend_end_try();

	RETVAL_TRUE;
    
	zend_destroy_file_handle(&handle);
}
