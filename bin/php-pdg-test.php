<?php

require __DIR__ . '/../vendor/autoload.php';

$graph_factory = new PhpPdg\Graph\Factory();
$base_graph_generator = new PhpPdg\BaseGraph\Generator();
$block_cfg_generator = new PhpPdg\ControlDependence\Block\Cfg\Generator($graph_factory);
$pdt_generator = new PhpPdg\PostDominatorTree\Generator($graph_factory);
$cdg_generator = new PhpPdg\ControlDependence\Block\Cdg\Generator($graph_factory);
$control_dependence_generator = new PhpPdg\ControlDependence\Generator($block_cfg_generator, $pdt_generator, $cdg_generator);
$data_dependence_generator = new PhpPdg\DataDependence\Generator();
$pdg_generator = new PhpPdg\Generator($graph_factory, $base_graph_generator, $control_dependence_generator, $data_dependence_generator);

$ast_parser = (new PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
$cfg_parser = new PHPCfg\Parser($ast_parser);

$testfilepath = __DIR__ . '/test.php';
$cfg_script = $cfg_parser->parse(file_get_contents($testfilepath), $testfilepath);
var_dump($pdg_generator->generate($cfg_script->main));
foreach ($cfg_script->functions as $cfg_func) {
	var_dump($pdg_generator->generate($cfg_func));
}
$i = 0;
