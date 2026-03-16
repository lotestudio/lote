<?php

declare(strict_types=1);

namespace App\Lote\Deploy;

use Dotenv\Dotenv;
use Exception;

class SetupConfiguration
{
    const string PUBLIC_BUILD = 'public/build';

    const string BUILD_TAR_GZ = 'build.tar.gz';

    private array $config = [];

    public function __construct(private readonly string $branch = 'staging')
    {
        $this->loadEnvironment();
        $this->setupServerConfig();
        $this->setupCommands();
        $this->setupBuildConfig();
    }

    private function loadEnvironment(): void
    {
        require __DIR__.'/../../../vendor/autoload.php';

        $dotenv = Dotenv::createImmutable(__DIR__.'/../../../');

        try {
            $dotenv->load();
            $dotenv->required([
                'DEPLOY_PRODUCTION_SERVER',
                'DEPLOY_PRODUCTION_PATH',
                'DEPLOY_STAGE_SERVER',
                'DEPLOY_STAGE_PATH',
            ])->notEmpty();
        } catch (Exception $exception) {
            echo $exception->getMessage();
            exit;
        }
    }

    private function setupServerConfig(): void
    {
        $this->config['server_port'] = $_ENV['DEPLOY_STAGE_PORT'];
        $this->config['server_no_port'] = $_ENV['DEPLOY_STAGE_SERVER'];
        $this->config['deploy_path'] = $_ENV['DEPLOY_STAGE_PATH'];

        if ($this->branch === 'main') {
            $this->config['server_port'] = $_ENV['DEPLOY_PRODUCTION_PORT'];
            $this->config['server_no_port'] = $_ENV['DEPLOY_PRODUCTION_SERVER'];
            $this->config['deploy_path'] = $_ENV['DEPLOY_PRODUCTION_PATH'];
            echo 'Proceed deploy to PRODUCTION';
        } else {
            echo 'Proceed deploy to STAGING';
        }

        $this->config['server'] = $this->config['server_no_port'].' -p '.$this->config['server_port'];
    }

    private function setupCommands(): void
    {
        $this->config['composer'] = 'composer';
        $this->config['php'] = 'php';
    }

    private function setupBuildConfig(): void
    {
        $this->config['localBuildDir'] = self::PUBLIC_BUILD;
        $this->config['archiveName'] = self::BUILD_TAR_GZ;
    }

    public function get(string $key)
    {
        return $this->config[$key] ?? null;
    }

    public function all(): array
    {
        return $this->config;
    }
}
