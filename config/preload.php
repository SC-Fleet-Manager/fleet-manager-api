<?php

if (file_exists(dirname(__DIR__).'/var/build/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__).'/var/build/prod/App_KernelProdContainer.preload.php';
} elseif (file_exists(dirname(__DIR__).'/var/build/beta/App_KernelBetaContainer.preload.php')) {
    require dirname(__DIR__).'/var/build/beta/App_KernelBetaContainer.preload.php';
}
