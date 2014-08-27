<?php
/**
 * Register SPL autoloading for classes and interfaces.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

spl_autoload_extensions('.php');
spl_autoload_register();
set_include_path(get_include_path().PATH_SEPARATOR.__DIR__);
