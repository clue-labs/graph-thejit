<?php

namespace Fhaculty\Graph\TheJit;

use Fhaculty\Graph\Graph;
use Exception;

class ForceDirectedVisualizer
{
    private $path = 'Jit/';

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function createScript()
    {
        $exporter = new Exporter($this->graph);
        $json = $exporter->exportJsonGraph();

        $script = "
var labelType, useGradients, nativeTextSupport, animate;

(function() {
  var ua = navigator.userAgent,
      iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
      typeOfCanvas = typeof HTMLCanvasElement,
      nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
      textSupport = nativeCanvasSupport
        && (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
  //I'm setting this based on the fact that ExCanvas provides text support for IE
  //and that as of today iPhone/iPad current text support is lame
  labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
  nativeTextSupport = labelType == 'Native';
  useGradients = nativeCanvasSupport;
  animate = !(iStuff || !nativeCanvasSupport);
})();

var Log = {
  elem: false,
  write: function(text){
    if (!this.elem)
      this.elem = document.getElementById('log');
    this.elem.innerHTML = text;
    this.elem.style.left = (500 - this.elem.offsetWidth / 2) + 'px';
  }
};


function init(){
  // init data
  var json = $json;
  // end

  // init ForceDirected
  var fd = new \$jit.ForceDirected({
    //id of the visualization container
    injectInto: 'infovis',
    //Enable zooming and panning
    //by scrolling and DnD
    Navigation: {
      enable: true,
      //Enable panning events only if we're dragging the empty
      //canvas (and not a node).
      panning: 'avoid nodes',
      zooming: 10 //zoom speed. higher is more sensible
    },
    // Change node and edge styles such as
    // color and width.
    // These properties are also set per node
    // with dollar prefixed data-properties in the
    // JSON structure.
    Node: {
      overridable: true
    },
    Edge: {
      overridable: true,
      color: '#23A4FF',
      lineWidth: 0.4
    },
    //Native canvas text styling
    Label: {
      type: labelType, //Native or HTML
      size: 10,
      style: 'bold'
    },
    //Add Tips
    Tips: {
      enable: true,
      onShow: function(tip, node) {
        //count connections
        var count = 0;
        node.eachAdjacency(function() { count++; });
        //display node info in tooltip

      }
    },
    //Number of iterations for the FD algorithm
    iterations: 200,
    //Edge length
    levelDistance: 130,
    // Add text to the labels. This method is only triggered
    // on label creation and only for DOM labels (not native canvas ones).
    onCreateLabel: function(domElement, node){
      domElement.innerHTML = node.name;
      var style = domElement.style;
      style.fontSize = '0.8em';
      style.color = '#ddd';
    },
    // Change node styles when DOM labels are placed
    // or moved.
    onPlaceLabel: function(domElement, node){
      var style = domElement.style;
      var left = parseInt(style.left);
      var top = parseInt(style.top);
      var w = domElement.offsetWidth;
      style.left = (left - w / 2) + 'px';
      style.top = (top + 10) + 'px';
      style.display = '';
    }
  });
  // load JSON data.
  fd.loadJSON(json);
  // compute positions incrementally and animate.
  fd.computeIncremental({
    iter: 40,
    property: 'end',
    onStep: function(perc){
      Log.write(perc + '% loaded...');
    },
    onComplete: function(){
      Log.write('done');
      fd.animate({
        modes: ['linear'],
        transition: \$jit.Trans.Elastic.easeOut,
        duration: 2500
      });
    }
  });
  // end
}";
        return $script;
    }

    public function createHtml()
    {
        $basepath = rtrim($this->path, '/');

        if (!is_dir($basepath)) {
            throw new Exception('Invalid basepath');
        }

        $script = $this->createScript();

        $html = <<<EOT
<!DOCTYPE html>
<html>
<head>
<title>Force Directed Graph</title>

<!-- CSS Files -->
<link type="text/css" href="$basepath/css/base.css" rel="stylesheet" />
<link type="text/css" href="$basepath/css/Hypertree.css" rel="stylesheet" />

<!--[if IE]><script language="javascript" type="text/javascript" src="$basepath/Extras/excanvas.js"></script><![endif]-->

<!-- JIT Library File -->
<script language="javascript" type="text/javascript" src="$basepath/jit.js"></script>

<script language="javascript" type="text/javascript">
$script
</script>
</head>

<body onload="init();">
<div id="container">

<div id="left-container">



        <div class="text">
        <h4>
Force Directed Static Graph
        </h4>

            A static JSON Graph structure is used as input for this visualization.<br /><br />
            You can <b>zoom</b> and <b>pan</b> the visualization by <b>scrolling</b> and <b>dragging</b>.<br /><br />
            You can <b>change node positions</b> by <b>dragging the nodes around</b>.<br /><br />
            The clicked node's connections are displayed in a relations list in the right column.<br /><br />
            The JSON static data is customized to provide different node types, colors and widths.

        </div>

        <div id="id-list"></div>


<div style="text-align:center;"><a href="example1.js">See the Example Code</a></div>
</div>

<div id="center-container">
    <div id="infovis"></div>
</div>

<div id="right-container">

<div id="inner-details"></div>

</div>

<div id="log"></div>
</div>
</body>
</html>
EOT;
        return $html;
    }

    public function display()
    {
        $name = tempnam(sys_get_temp_dir(), 'thejit');
        file_put_contents($name, $this->createHtml());

        system('firefox ' . escapeshellarg($name));
    }
}
