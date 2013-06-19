<?php

namespace Fhaculty\Graph\TheJit;

use Fhaculty\Graph\Exception\InvalidArgumentException;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Loader\File;
use Exception;

/**
 *
 * @link http://philogb.github.io/jit/static/v20/Docs/files/Loader/Loader-js.html
 */
class Loader/* extends File*/
{
    private $stream;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Invalid argument given, expected stream resource to read from');
        }
        $this->stream = $stream;
    }

    private function getContents()
    {
        $contents = stream_get_contents($this->stream);

        if ($contents === false) {
            throw new Exception('Unable to read file');
        }
        return $contents;
    }

    public function createGraph()
    {
        $contents = $this->getContents();
        $json = @json_decode($contents, false);
        if ($json === null || is_bool($json)) {
            var_dump($contents, $json);
            throw new Exception('Invalid/unparsable JSON');
        }

        $graph = new Graph();

        if (is_array($json)) {
            foreach ($json as $node) {
                $this->readNode($node, $graph);
            }
        } else if (is_object($json)) {
            $this->readNode($json, $graph);
        } else {
            throw new Exception('Invalid/unknown JSON');
        }


        return $graph;
    }

    private function readNode($node, Graph $graph)
    {
        $vertex = $graph->createVertex($node->id, true);
        $vertex->setLayoutAttribute('label', $node->name);

        // ignore $node->data ?

        if (isset($node->children)) {
            foreach ($node->children as $nodeChild) {
                $vertexChild = $this->readNode($nodeChild, $graph);

                $vertex->createEdgeTo($vertexChild);
            }
        } elseif (isset($node->adjacencies)) {
            foreach ($node->adjacencies as $nodeAdjacent) {
                if (is_object($nodeAdjacent)) {
                    $nodeAdjacent = $nodeAdjacent->nodeTo;
                }
                $vertexAdjacent = $this->createVertex($nodeAdjacent);

                $vertex->createEdgeTo($vertexAdjacent);
            }
        }

        return $vertex;
    }
}
