<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/crontab.php';

// Configs  (You need to configure these)

$remoteUser = 'deployer';   // the user that will be used to connect to remote server and deploy the app
$sudoPassword = '';  // the sudo password of the remote user

$deployPath = '~/app';      // the path where the app will be deployed on the remote server

$host = '1.2.3.4';    // the host of the remote server (can be an IP or domain)
$domain = 'yourdomain.com';   // the domain of the app

$repository = 'git@github.com:username/saasykit.git';      // has to be in the SSH format
$subDirectory = '';    // the subdirectory of the repository where the app is located (this is the directory that contains the composer.json file). Leave empty if the app is in the root of the repository (by default)

$phpVersion = '8.2'; // the version of PHP to be installed on the server

// End of configs
/////////////////////////////////////
/////////////////////////////////////

set('repository', $repository);
set('sub_directory', $subDirectory);

set('nodejs_version', 'node_18.x');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

host($host)
    ->set('remote_user', $remoteUser)
    ->set('deploy_path', $deployPath)
    ->set('sudo_password', $sudoPassword)
    ->set('domain', $domain)
    ->set('public_path', 'public')
    ->set('php_version', $phpVersion);

desc('Install & build npm packages');
task('npm:build', function () {
    run('cd {{release_path}} && npm ci && npm run build');
});

desc('Provision extra PHP packages');
task('provision:php-extra', function () {
    $version = get('php_version');
    info("Installing Extra PHP $version packages");
    $packages = [
        "php$version-redis",
    ];

    run('apt-get install -y '.implode(' ', $packages), ['env' => ['DEBIAN_FRONTEND' => 'noninteractive']]);
})->verbose()
    ->limit(1);

desc('Provision supervisor');
task('provision:supervisor', function () use ($remoteUser, $deployPath) {
    info('Installing Supervisor');

    run('apt-get install -y supervisor', ['env' => ['DEBIAN_FRONTEND' => 'noninteractive']]);

    $supervisorConfig = <<<'EOF'
[program:horizon]
process_name=%(program_name)s
command=php {{deployPath}}/current/artisan horizon
autostart=true
autorestart=true
user={{user}}
redirect_stderr=true
stdout_logfile={{deployPath}}/log/horizon.log
stopwaitsecs=60
EOF;

    $deployPathRelativeToDeployerUser = str_replace('~', '/home/'.$remoteUser, $deployPath);

    $supervisorConfig = str_replace('{{deployPath}}', $deployPathRelativeToDeployerUser, $supervisorConfig);
    $supervisorConfig = str_replace('{{user}}', $remoteUser, $supervisorConfig);

    $supervisorConfigPath = '/etc/supervisor/conf.d/horizon.conf';

    run("echo '$supervisorConfig' > $supervisorConfigPath");

    run('supervisorctl reread');
    run('supervisorctl update');
    run('supervisorctl start horizon');
})->verbose()
    ->limit(1);

desc('Fixes a common bug with AWS EC2 instances that causes SSH to fail');
task('provision:fix-aws-ssh', function () {
    $authorizedKeys = run('cat /home/deployer/.ssh/authorized_keys');

    $searchSting = 'no-port-forwarding,no-agent-forwarding,no-X11-forwarding,command="echo \'Please login as the user \"ubuntu\" rather than the user \"root\".\';echo;sleep 10;exit 142" ';

    if (str_contains($authorizedKeys, $searchSting)) {
        $authorizedKeys = str_replace($searchSting, '', $authorizedKeys);
        $authorizedKeys = trim($authorizedKeys);
        run('echo "$KEY" > /home/deployer/.ssh/authorized_keys', ['env' => ['KEY' => $authorizedKeys]]);
    }
})->verbose()
    ->limit(1);

desc('Generate sitemap');
task('deploy:sitemap', artisan('app:generate-sitemap', ['skipIfNoEnv']));

// seed database
after('artisan:migrate', 'artisan:db:seed');

//npm
after('artisan:migrate', 'npm:build');

//php
after('provision:php', 'provision:php-extra');
after('provision:verify', 'provision:supervisor');
after('provision:deployer', 'provision:fix-aws-ssh');

after('deploy:success', 'artisan:horizon:terminate'); // to restart horizon after deploy
after('deploy:success', 'crontab:sync');
after('deploy:success', 'deploy:sitemap');

after('deploy:failed', 'deploy:unlock');

add('crontab:jobs', [
    '* * * * * cd {{current_path}} && {{bin/php}} artisan schedule:run >> /dev/null 2>&1',
]);
