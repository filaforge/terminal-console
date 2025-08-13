<?php

return [
    // If true, any command can run (unsafe). If false, only binaries in 'allowed_binaries' may run.
    'allow_any' => false,

    // Allowed binaries when 'allow_any' is false. Keep this tight in production.
    'allowed_binaries' => [
        'php',
        'ls',
        'pwd',
        'cd',
        'whoami',
        'composer',
        'git',
        'cat',
        'grep',
        'find',
        'mkdir',
        'rmdir',
        'cp',
        'mv',
        'rm',
        'echo',
        'head',
        'tail',
        'tree',
        'df',
        'du',
        'ps',
        'uname',
    ],

    // Working directory and timeout (seconds)
    'working_directory' => base_path(),
    'timeout' => 60,

    // Keep only the last N history entries in memory
    'max_history' => 100,
];


