<?php

// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!
// This file is included via auto_prepend_file in php.ini - and it is used for
// profiling.
// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!

function startExcimer() {
    static $excimer;
    if (!class_exists(\ExcimerProfiler::class)) {
        // excimer.so profiling extension not loaded.
        return;
    }

    $excimer = new ExcimerProfiler();
    $excimer->setPeriod( 0.001 ); // 1ms
    $excimer->setEventType( EXCIMER_REAL ); // OR: EXCIMER_CPU, but does not work on FreeBSD.
    $excimer->start();
    register_shutdown_function( function () use ( $excimer ) {
        $excimer->stop();
        $data = $excimer->getLog()->formatCollapsed();
        file_put_contents('/tracing/_traces/' . getmypid(), $data, FILE_APPEND);
    } );
}

// HINT: to start PHP continuous profiling, comment-in the following line.
// startExcimer();
