<?php

declare(strict_types=1);

namespace App\Lote\Services\DevDBSync;

use Spatie\Ssh\Ssh;

class SshClients
{
    protected string $prod_host;

    protected string $staging_user;

    protected string $staging_host;

    protected string $prod_user;

    protected mixed $prod_port;

    protected mixed $staging_port;

    public function __construct()
    {
        if (! app()->isLocal()) {
            throw new \Exception('This run only on local environment');
        }

        [$this->staging_user, $this->staging_host] = explode('@', $_ENV['DEPLOY_STAGE_SERVER']);
        [$this->prod_user, $this->prod_host] = explode('@', $_ENV['DEPLOY_PRODUCTION_SERVER']);

        $this->prod_port = $_ENV['DEPLOY_PRODUCTION_PORT'];
        $this->staging_port = $_ENV['DEPLOY_STAGE_PORT'];

    }

    public static function getStaging(): Ssh
    {
        $sshClients = new SshClients;

        return Ssh::create($sshClients->staging_user, $sshClients->staging_host, $sshClients->staging_port);
    }

    public static function getProduction(): Ssh
    {
        $sshClients = new SshClients;

        return Ssh::create($sshClients->prod_user, $sshClients->prod_host, $sshClients->prod_port);
    }
}
