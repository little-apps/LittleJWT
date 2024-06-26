<?php

namespace LittleApps\LittleJWT\Commands;

use Illuminate\Console\Command;
use LittleApps\LittleJWT\Commands\Concerns\GeneratesEnvVariables;
use LittleApps\LittleJWT\Factories\OpenSSLBuilder;

class GeneratePemCommand extends Command
{
    use GeneratesEnvVariables;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'littlejwt:pem
        {file? : The destination file for the private key. If not specified, the key is outputted.}
        {--key-type=rsa : The type of private key to generate. Available options: rsa (default), dsa, dh, or ec.}
        {--key-curve=prime256v1 : The curve to use to generate the private key. See https://www.php.net/openssl_get_curve_names for possible options.}
        {--key-bits= : Number of bits to use to generate the private key. If not specified, the OpenSSL default is used.}
        {--d|display : Displays the generated environment variables instead of saving them to the .env file.}
        {--force : Forces the file to be overwritten if it already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a private key for Little JWT';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(OpenSSLBuilder $builder)
    {
        $privKey = $builder->generatePrivateKey();
        $pem = $builder->exportPrivateKey($privKey);

        $file = $this->argument('file');

        if (is_null($file)) {
            $this->line($pem);

            return 0;
        }

        if (! $this->option('force') && file_exists($file)) {
            $this->error('File "%s" already exists. Pass the --force option to overwrite it.');

            return 1;
        }

        if (file_put_contents($file, $pem) === false) {
            $this->error(sprintf('An error occurred writing to file "%s".', $file));

            return 1;
        }

        $vars = [
            'LITTLEJWT_KEY_FILE_TYPE' => 'pem',
            'LITTLEJWT_KEY_FILE_PATH' => realpath($file),
            'LITTLEJWT_KEY_FILE_SECRET' => '',
        ];

        if ($this->option('display')) {
            $this->info('Generated environment variables:');
            $this->line('');

            foreach ($vars as $key => $value) {
                $this->info($this->createLineForEnvFile($key, $value));
            }
        } else {
            if ($this->updateEnvFile($this->envPath(), $vars)) {
                $this->info('The .env file has been updated.');
            } else {
                $this->error(sprintf('An error occurred updating the "%s" file.', $this->envPath()));

                return 1;
            }
        }

        return 0;
    }
}
