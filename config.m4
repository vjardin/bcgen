dnl
dnl $Id$
dnl

PHP_ARG_ENABLE(bcgen, whether to enable Zend bcgen support,
[  --disable-bcgen       Disable Zend bcgen support], yes)

PHP_ARG_ENABLE(opcache-file, whether to enable file based caching,
[  --disable-bcgen-file  Disable file based caching], yes, no)

PHP_ARG_ENABLE(huge-code-pages, whether to enable copying PHP CODE pages into HUGE PAGES,
[  --disable-huge-code-pages
                          Disable copying PHP CODE pages into HUGE PAGES], yes, no)

if test "$PHP_BCGEN" != "no"; then

  if test "$PHP_BCGEN_FILE" = "yes"; then
    AC_DEFINE(HAVE_BCGEN_FILE_CACHE, 1, [Define to enable file based caching (experimental)])
  fi

  if test "$PHP_HUGE_CODE_PAGES" = "yes"; then
    AC_DEFINE(HAVE_HUGE_CODE_PAGES, 1, [Define to enable copying PHP CODE pages into HUGE PAGES (experimental)])
  fi

  AC_CHECK_HEADERS([unistd.h sys/uio.h])

  PHP_NEW_EXTENSION(bcgen,
	ZendAccelerator.c \
	zend_accelerator_debug.c \
	zend_accelerator_module.c \
	zend_persist.c \
	zend_persist_calc.c \
	zend_file_cache.c \
	zend_shared_alloc.c \
	zend_accelerator_util_funcs.c \
	Optimizer/zend_optimizer.c \
	Optimizer/pass1_5.c \
	Optimizer/pass2.c \
	Optimizer/pass3.c \
	Optimizer/optimize_func_calls.c \
	Optimizer/block_pass.c \
	Optimizer/optimize_temp_vars_5.c \
	Optimizer/nop_removal.c \
	Optimizer/compact_literals.c \
	Optimizer/zend_cfg.c \
	Optimizer/zend_dfg.c \
	Optimizer/dfa_pass.c \
	Optimizer/zend_ssa.c \
	Optimizer/zend_inference.c \
	Optimizer/zend_func_info.c \
	Optimizer/zend_call_graph.c \
	Optimizer/sccp.c \
	Optimizer/scdf.c \
	Optimizer/dce.c \
	Optimizer/escape_analysis.c \
	Optimizer/compact_vars.c \
	Optimizer/zend_dump.c,
	shared,,-DZEND_ENABLE_STATIC_TSRMLS_CACHE=1,,yes)

  PHP_ADD_BUILD_DIR([$ext_builddir/Optimizer], 1)
fi
