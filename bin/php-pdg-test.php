<?php

require __DIR__ . '/../vendor/autoload.php';

$graph_factory = new PhpPdg\Graph\Factory();
$block_cfg_generator = new PhpPdg\ControlDependence\Block\Cfg\Generator($graph_factory);
$pdt_generator = new PhpPdg\PostDominatorTree\Generator($graph_factory);
$cdg_generator = new PhpPdg\ControlDependence\Block\Cdg\Generator($graph_factory);
$control_dependence_generator = new PhpPdg\ControlDependence\Generator($block_cfg_generator, $pdt_generator, $cdg_generator);
$data_dependence_generator = new PhpPdg\DataDependence\Generator();
$pdg_generator = new PhpPdg\Generator($graph_factory, $control_dependence_generator, $data_dependence_generator);

$ast_parser = (new PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
$cfg_parser = new PHPCfg\Parser($ast_parser);

$testfilepath = __DIR__ . '/test.php';
$cfg_script = $cfg_parser->parse(file_get_contents($testfilepath), $testfilepath);

$normalizer = new \PhpPdg\Normalization\Normalizer(new \PhpPdg\Graph\Normalization\Normalizer());
echo json_encode(array_map(function ($func) use ($normalizer, $pdg_generator) {
	return $normalizer->normalizeFunc($pdg_generator->generate($func));
}, array_merge([$cfg_script->main], $cfg_script->functions)), JSON_PRETTY_PRINT);
