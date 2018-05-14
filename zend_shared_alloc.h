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

#ifndef ZEND_SHARED_ALLOC_H
#define ZEND_SHARED_ALLOC_H

#include "zend.h"
#include "ZendAccelerator.h"

/* copy into shared memory */
void *_zend_shared_memdup(void *p, size_t size, zend_bool free_source);
int  zend_shared_memdup_size(void *p, size_t size);

typedef union _align_test {
	void   *ptr;
	double  dbl;
	zend_long  lng;
} align_test;

#if ZEND_GCC_VERSION >= 2000
# define PLATFORM_ALIGNMENT (__alignof__(align_test) < 8 ? 8 : __alignof__(align_test))
#else
# define PLATFORM_ALIGNMENT (sizeof(align_test))
#endif

#define ZEND_ALIGNED_SIZE(size) \
	ZEND_MM_ALIGNED_SIZE_EX(size, PLATFORM_ALIGNMENT)

/* old/new mapping functions */
void zend_shared_alloc_init_xlat_table(void);
void zend_shared_alloc_destroy_xlat_table(void);
void zend_shared_alloc_clear_xlat_table(void);
void zend_shared_alloc_register_xlat_entry(const void *old, const void *new);
void *zend_shared_alloc_get_xlat_entry(const void *old);

#endif /* ZEND_SHARED_ALLOC_H */
