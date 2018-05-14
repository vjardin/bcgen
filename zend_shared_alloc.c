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

#include <errno.h>
#include "ZendAccelerator.h"
#include "zend_shared_alloc.h"
#ifdef HAVE_UNISTD_H
# include <unistd.h>
#endif
#include <fcntl.h>
#ifndef ZEND_WIN32
# include <sys/types.h>
# include <dirent.h>
# include <signal.h>
# include <sys/stat.h>
# include <stdio.h>
#endif

#ifndef ZEND_WIN32
#ifdef ZTS
static MUTEX_T zts_lock;
#endif
int lock_file;
static char lockfile_name[MAXPATHLEN];
#endif

int zend_shared_memdup_size(void *source, size_t size)
{
	void *old_p;

	if ((old_p = zend_hash_index_find_ptr(&ZCG(xlat_table), (zend_ulong)source)) != NULL) {
		/* we already duplicated this pointer */
		return 0;
	}
	zend_shared_alloc_register_xlat_entry(source, source);
	return ZEND_ALIGNED_SIZE(size);
}

void *_zend_shared_memdup(void *source, size_t size, zend_bool free_source)
{
	void *old_p, *retval;

	if ((old_p = zend_hash_index_find_ptr(&ZCG(xlat_table), (zend_ulong)source)) != NULL) {
		/* we already duplicated this pointer */
		return old_p;
	}
	retval = ZCG(mem);
	ZCG(mem) = (void*)(((char*)ZCG(mem)) + ZEND_ALIGNED_SIZE(size));
	memcpy(retval, source, size);
	zend_shared_alloc_register_xlat_entry(source, retval);
	if (free_source) {
		efree(source);
	}
	return retval;
}

void zend_shared_alloc_init_xlat_table(void)
{
	/* Prepare translation table */
	zend_hash_init(&ZCG(xlat_table), 128, NULL, NULL, 0);
}

void zend_shared_alloc_destroy_xlat_table(void)
{
	/* Destroy translation table */
	zend_hash_destroy(&ZCG(xlat_table));
}

void zend_shared_alloc_clear_xlat_table(void)
{
	zend_hash_clean(&ZCG(xlat_table));
}

void zend_shared_alloc_register_xlat_entry(const void *old, const void *new)
{
	zend_hash_index_add_new_ptr(&ZCG(xlat_table), (zend_ulong)old, (void*)new);
}

void *zend_shared_alloc_get_xlat_entry(const void *old)
{
	void *retval;

	if ((retval = zend_hash_index_find_ptr(&ZCG(xlat_table), (zend_ulong)old)) == NULL) {
		return NULL;
	}
	return retval;
}
