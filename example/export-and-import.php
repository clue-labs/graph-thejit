<?php

use Fhaculty\Graph\TheJit\ForceDirectedVisualizer;
use Fhaculty\Graph\TheJit\Loader;
use Fhaculty\Graph\TheJit\Exporter;
use Fhaculty\Graph\GraphViz;
use Fhaculty\Graph\Graph;

require __DIR__ . '/../vendor/autoload.php';




// // 1 -> 2 -> 3
// $graph = new Graph();
// $v1 = $graph->createVertex(1);
// $v2 = $graph->createVertex(2);
// $v3 = $graph->createVertex(3);
// $v1->createEdgeTo($v2);
// $v2->createEdgeTo($v3);

$loader = new Loader(fopen(__DIR__ . '/data-tree.json', 'r'));
$graph = $loader->createGraph();

$visualizer = new ForceDirectedVisualizer($graph);
$visualizer->setPath(__DIR__ . '/Jit');
$visualizer->display();

// exit();
// $graphviz = new GraphViz($graph);
// $graphviz->display();

$exporter = new Exporter($graph);

$json = $exporter->exportJsonTree();
var_dump($json);

$stream = fopen('php://memory', 'r+');
fwrite($stream, $json);
fseek($stream, 0);

$loader = new Loader($stream);
$graph2 = $loader->createGraph();

$graphviz2 = new GraphViz($graph2);
$graphviz2->display();
