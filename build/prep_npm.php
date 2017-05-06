<?php
	#
	# this script builds directory structures to allow publishing each image set to npm
	# individually. we do this because there are too big to include in a single package.
	#

	$dir = __DIR__;

	@mkdir("$dir/../packages");

	prep_package('apple');
	prep_package('twitter');
	prep_package('google');
	prep_package('emojione');
	prep_package('facebook');
	prep_package('messenger');

	echo "All done!\n";


	function prep_package($name){

		global $dir;

		shell_exec("rm -rf {$dir}/../packages/{$name}");
		mkdir("{$dir}/../packages/{$name}");

		mkdir("{$dir}/../packages/{$name}/img");
		mkdir("{$dir}/../packages/{$name}/img/{$name}");

		mkdir("{$dir}/../packages/{$name}/img/{$name}/64");
		mkdir("{$dir}/../packages/{$name}/img/{$name}/sheets");
		mkdir("{$dir}/../packages/{$name}/img/{$name}/sheets-128");
		mkdir("{$dir}/../packages/{$name}/img/{$name}/sheets-256");

		copy("{$dir}/../sheet_{$name}_16.png", "{$dir}/../packages/{$name}/img/{$name}/sheets/16.png");
		copy("{$dir}/../sheet_{$name}_20.png", "{$dir}/../packages/{$name}/img/{$name}/sheets/20.png");
		copy("{$dir}/../sheet_{$name}_32.png", "{$dir}/../packages/{$name}/img/{$name}/sheets/32.png");
		copy("{$dir}/../sheet_{$name}_64.png", "{$dir}/../packages/{$name}/img/{$name}/sheets/64.png");

		copy("{$dir}/../sheets-indexed-128/sheet_{$name}_16_indexed_128.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-128/16.png");
		copy("{$dir}/../sheets-indexed-128/sheet_{$name}_20_indexed_128.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-128/20.png");
		copy("{$dir}/../sheets-indexed-128/sheet_{$name}_32_indexed_128.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-128/32.png");
		copy("{$dir}/../sheets-indexed-128/sheet_{$name}_64_indexed_128.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-128/64.png");

		copy("{$dir}/../sheets-indexed-256/sheet_{$name}_16_indexed_256.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-256/16.png");
		copy("{$dir}/../sheets-indexed-256/sheet_{$name}_20_indexed_256.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-256/20.png");
		copy("{$dir}/../sheets-indexed-256/sheet_{$name}_32_indexed_256.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-256/32.png");
		copy("{$dir}/../sheets-indexed-256/sheet_{$name}_64_indexed_256.png", "{$dir}/../packages/{$name}/img/{$name}/sheets-256/64.png");

		shell_exec("cp {$dir}/../img-{$name}-64/*.png {$dir}/../packages/{$name}/img/{$name}/64/");

		$files = array(
			'LICENSE',
			'README.md',
			'CHANGES.md',
			'emoji.json',
			'emoji_pretty.json',
		);

		foreach ($files as $file){
			copy("{$dir}/../{$file}", "{$dir}/../packages/{$name}/{$file}");
		}

		$version = trim(shell_exec("node -e \"console.log(require('../package.json').version);\""));

		$package = file_get_contents("{$dir}/package.json.template");
		$package = str_replace(array('#NAME#', '#VERSION#'), array($name, $version), $package);

		$fh = fopen("{$dir}/../packages/{$name}/package.json", 'w');
		fputs($fh, $package);
		fclose($fh);
	}
