<?php

declare(strict_types=1);

namespace App\Lote\Services\DevDBSync;

use Symfony\Component\Process\Process;

// This class is used to dump the database from the production or staging
class Dumper
{
    public $file_name = 'sync_db';

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (! app()->isLocal()) {
            throw new \Exception('This run only on local environment');
        }
    }

    public function download(bool $isProduction): string
    {
        $ssh = $this->getSshClient($isProduction);
        $remote_path = $this->getRemotePath($isProduction);

        $local_path = database_path('snapshots/');

        $download = $ssh->download(
            $remote_path.'/database/snapshots/'.$this->file_name.'.sql',
            $local_path.$this->file_name.'.sql',
        );

        return $remote_path.($download->isSuccessful() ? ' db file is downloaded' : ' db file NOT downloaded');

    }

    /**
     * @throws \Exception
     */
    public function uploadToStaging(): string
    {
        $staging = $_ENV['DEPLOY_STAGE_SERVER'];
        $remote_path = $this->getRemotePath(false);
        $local_file = database_path('snapshots/').$this->file_name.'.sql';
        $staging_folder = $staging.':'.$remote_path.'/database/snapshots/';

        $command = 'scp '.$local_file.' '.$staging_folder;

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run();

        return $remote_path.($process->isSuccessful() ? ' db file is moved to staging' : ' db file NOT moved to staging');

    }

    /**
     * @throws \Exception
     */
    public function dumpDb(bool $isProduction): string
    {

        $ssh = $this->getSshClient($isProduction);
        $remote_path = $this->getRemotePath($isProduction);

        $process = $ssh->execute([
            'cd '.$remote_path,
            'php artisan snapshot:create '.$this->file_name,
        ]);

        return $remote_path.($process->isSuccessful() ? ' db file is exported' : ' db file NOT exported');
    }

    /**
     * @throws \Exception
     */
    public function deleteDumpFile(bool $isProduction): string
    {

        $ssh = $this->getSshClient($isProduction);
        $remote_path = $this->getRemotePath($isProduction);

        $process = $ssh->execute([
            'cd '.$remote_path,
            'php artisan snapshot:delete '.$this->file_name,
        ]);

        return $remote_path.($process->isSuccessful() ? ' db file is deleted' : ' db file NOT deleted');
    }

    public function migrateDbOnStaging(): string
    {
        $ssh = SshClients::getStaging();
        $remote_path = $this->getRemotePath(false);

        $process = $ssh->execute([
            'cd '.$remote_path,
            'php artisan snapshot:load '.$this->file_name,
            'php artisan migrate --force',
            'php artisan snapshot:delete '.$this->file_name,
        ]);

        return $remote_path.($process->isSuccessful() ? ' db migrated' : ' db NOT migrated');
    }

    protected function getRemotePath(bool $isProduction)
    {
        return $isProduction ? $_ENV['DEPLOY_PRODUCTION_PATH'] : $_ENV['DEPLOY_STAGE_PATH'];
    }

    protected function getSshClient(bool $isProduction): \Spatie\Ssh\Ssh
    {
        return $isProduction ? SshClients::getProduction() : SshClients::getStaging();
    }
}
