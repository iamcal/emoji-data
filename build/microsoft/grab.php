<?php
shell_exec("rm -f ../../img-microsoft-256/*.png");

$rmap = [];
foreach (glob('fluent-emoji/assets/*/metadata.json') as $meta) {
	$emoji_dir = dirname($meta);
	$data = json_decode(file_get_contents($meta), true);
	$base = basename($emoji_dir);
	if ($data['cldr'] === 'O button (blood type)') {
		$base = $data['cldr'];
	}
	$base = '3D/' . strtolower(str_replace(' ', '_', $base)) . '_3d';

	if (isset($data['unicodeSkintones'])) {
		foreach (['Default', 'Light', 'Medium-Light', 'Medium', 'Medium-Dark', 'Dark'] as $i => $variant) {
			$path = join('/', [$emoji_dir, $variant, $base . '_' . strtolower($variant) . '.png']);
			if (!file_exists($path)) {
				echo "$path not found!\n";
				continue;
			}
			$converted = convert_unicode($data['unicodeSkintones'][$i]);
			$rmap[$converted] = $path;
		}
	} else {
		$converted = convert_unicode($data['unicode']);
		$path = join('/', [$emoji_dir, $base . '.png']);
		if (!file_exists($path)) {
			echo "$path not found!\n";
			continue;
		}
		$rmap[$converted] = $path;
	}
}

echo sizeof($rmap) . "\n";

$json = file_get_contents('../../emoji.json');
$data = json_decode($json, true);

$failed = 0;
foreach ($data as $row) {
	if (strlen($row['image'])) {
		if ($row['non_qualified']) {
			fetch($rmap, $row['unified'], $row['image']);
		} else {
			fetch($rmap, $row['unified'], $row['image']);
		}
	}

	if (isset($row['skin_variations'])) {
		foreach ($row['skin_variations'] as $row2) {
			fetch($rmap, $row2['unified'], $row2['image']);
		}
	}
}

echo "\nDONE\n";

function convert_unicode(string $src): string
{
	return strtoupper(str_replace(' ', '-', $src));
}

function fetch(array $rmap, string $qualified, string $target_file)
{
	$dst = "../../img-microsoft-256/$target_file";

	if (!isset($rmap[$qualified])) {
		echo "Not found: $qualified\n";
		return;
	}

	$src = $rmap[$qualified];

	copy($src, $dst);
}
