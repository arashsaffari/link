<?php namespace CodeigniterExt\Link\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Paths;

class Link extends BaseCommand
{
	protected $group        = 'Link';
	protected $name         = 'link:create';
	protected $description  = "Create a symbolic link from subfolder in writable folder to public \n php spark link:create -t uploads -l uploads_in_public";
	protected $usage        = 'link:create [Options]';
	protected $arguments    = [
		// 'link'		=> 'writable/*',
		// 'target'	=> 'public/*'
	];
	protected $options 		= [
		'-t'	=> 'set link from subfolder in writable/*',
		'-l'	=> 'set target from subfolder in public/*',
	];

	
	public function run(array $params)
	{
		//
		// get inputs
		//

		$config = new Paths();

		$writableDir = $config->writableDirectory;
		$publicDir =  $config->appDirectory . '/../public';


		$target = trim($params['-t'] ?? CLI::getOption('t'));
		$link = trim($params['-l'] ?? CLI::getOption('l'));

		
		$error = false;


		if ( empty($link) || !is_string($link) )
		{
			CLI::error('please set link name with -l NAME');
			$error = true;
		}

		$link = set_realpath($publicDir) . $link;


		if ( empty($target) || !is_string($target) )
		{
			CLI::error('please set target folder with -t NAME');
			$error = true;
		}

		$target = set_realpath($writableDir) . $target;

		CLI::write("Target: " . $target);
		CLI::write("Link: " . $link);
		


		if (file_exists($link)) {
			CLI::error('The target "' . $link . '" already exists.');
			$error = true;
        }

		if ($error) return;

		if (! $this->windows_os()) {
			if(! symlink($target, $link))
			{
				CLI::error('Link could not be created');
				exit;	
			}
		}
		else
		{
			$mode = $this->isDirectory($target) ? 'J' : 'H';
        	exec("mklink /{$mode} " . escapeshellarg($link) . escapeshellarg($target));
		}

		CLI::write( 'Link was created.' , 'green');

		CLI::newLine(1);

	}

	function windows_os()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
	}
	
	public function isDirectory($directory)
    {
        return is_dir($directory);
    }

}
