<?php

// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!
// This file is included via auto_prepend_file in php.ini - and it is used for
// profiling.
// !!!!!!!!!!!!! DO NOT DELETE THIS FILE !!!!!!!!!!!!!!!!!!!!

function startExcimer()
{
    static $excimer;
    if (!class_exists(\ExcimerProfiler::class)) {
        // excimer.so profiling extension not loaded.
        return;
    }

    $excimer = new ExcimerProfiler();
    $excimer->setPeriod(1); // Change if needed

    $excimer->setEventType(EXCIMER_REAL); // OR: EXCIMER_CPU, but does not work on FreeBSD.
    $excimer->start();

    register_shutdown_function(function () use ($excimer) {
        $excimer->stop();
        $data = $excimer->getLog()->formatCollapsed();

        # return early if no traces collected
        if($data === ""){
            return;
        }

        $url = getenv("PROFILING_VECTOR_URL", "0.0.0.0:8234");
        $username = getenv("PROFILING_VECTOR_USER", "user"); // vector user
        $password = getenv("PROFILING_VECTOR_PASSWORD", "password"); // vector password

        if ($_SERVER && isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_METHOD']) {
            $request_path = $_SERVER['REQUEST_URI'];
            $http_method = $_SERVER['REQUEST_METHOD'];
        }

        $ch = curl_init($url);
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'traces' => $data,
            // set to null so default of database is used
            'request_path' => $request_path ?? null,
            'http_method' => $http_method ?? null,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_exec($ch);

        curl_close($ch);
    });
}

// HINT: to stop PHP continuous profiling, comment-in the following line.
// startExcimer();
