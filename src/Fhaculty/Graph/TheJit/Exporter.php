<?php

namespace Fhaculty\Graph\TheJit;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Algorithm\Tree\OutTree as Tree;
use Fhaculty\Graph\Exception\UnexpectedValueException;

class Exporter
{
    private $graph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    // http://philogb.github.io/jit/static/v20/Jit/Examples/Spacetree/example1.code.html
    public function exportTree()
    {
        $tree = new Tree($this->graph);
        if (!$tree->isTree()) {
            throw new UnexpectedValueException('Graph does not represent a valid tree');
        }

        $root = $tree->getVertexRoot();
        return $this->exportVertexTree($root, $tree);
    }

    public function exportJsonTree()
    {
        return json_encode($this->exportTree());
    }

    // http://philogb.github.io/jit/static/v20/Docs/files/Loader/Loader-js.html#Loader.loadJSON
    public function exportGraph()
    {
        $ret = array();
        foreach ($this->graph->getVertices() as $vertex) {
            $ret []= $this->exportVertexAdjacencies($vertex);
        }
        return $ret;
    }

    public function exportJsonGraph()
    {
        return json_encode($this->exportGraph());
    }

    private function exportVertexAdjacencies(Vertex $vertex)
    {
        return array(
            'id' => $vertex->getId(),
            'name' => $vertex->getId(),
            'data' => array(),
            'adjacencies' => array_keys($vertex->getVerticesEdgeTo())
        );
    }

    // http://philogb.github.io/jit/static/v20/Docs/files/Loader/Loader-js.html#Loader.loadJSON
    private function exportVertexTree(Vertex $vertex, Tree $tree)
    {
        $children = array();
        // recursively export vertex children
        foreach ($tree->getVerticesChildren($vertex) as $child) {
            $children []= $this->exportVertexTree($child, $tree);
        }

        return array(
            'id' => $vertex->getId(),
            'name' => $vertex->getId(),
            'data' => array(),
            'children' => $children
        );
    }
}
