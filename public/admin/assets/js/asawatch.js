// AsaWatch Admin — interaksi kecil (segmented control, chart switch, toggle sandi)
document.addEventListener('click', function (e) {
  var seg = e.target.closest('.hw-seg button');
  if (seg) {
    seg.parentElement.querySelectorAll('button').forEach(function (b) { b.classList.remove('active'); });
    seg.classList.add('active');
    var target = seg.parentElement.dataset.target;
    var key = seg.dataset.range;
    if (target && key) drawChart(document.querySelector(target), key);
  }
  var eye = e.target.closest('[data-toggle-password]');
  if (eye) {
    var input = document.querySelector(eye.dataset.togglePassword);
    var shown = input.type === 'text';
    input.type = shown ? 'password' : 'text';
    eye.querySelector('i').className = shown ? 'bi bi-eye' : 'bi bi-eye-slash';
  }
});

var SERIES = {
  harian: { axis:['00:00','04:00','08:00','12:00','16:00','20:00','24:00'],
    hr:[68,70,72,78,81,79,76,80,77,75,73,71,69],
    glu:[92,95,98,112,126,118,110,121,115,108,104,99,96],
    bp:[108,110,112,118,124,121,119,123,120,116,114,111,109] },
  mingguan: { axis:['Sen','Sel','Rab','Kam','Jum','Sab','Min'],
    hr:[74,78,76,82,79,75,77], glu:[108,118,112,131,124,110,115], bp:[116,121,118,128,123,115,119] },
  bulanan: { axis:['Mg 1','Mg 2','Mg 3','Mg 4'],
    hr:[76,79,77,80], glu:[112,120,116,125], bp:[117,122,119,124] }
};

function points(vals, w, h, min, max) {
  return vals.map(function (v, i) {
    var x = (i / (vals.length - 1)) * w;
    var y = h - ((v - min) / (max - min)) * h;
    return x.toFixed(1) + ',' + y.toFixed(1);
  }).join(' ');
}

function drawChart(svg, key) {
  if (!svg) return;
  var s = SERIES[key] || SERIES.harian;
  var vbH = parseFloat(svg.dataset.vbHeight || 210);
  var h = parseFloat(svg.dataset.plotHeight || 170);
  var w = Math.max(svg.clientWidth || svg.parentElement.clientWidth || 640, 320);

  svg.setAttribute('viewBox', '0 0 ' + w + ' ' + vbH);
  svg.querySelectorAll('.hw-grid line').forEach(function (l) {
    if (l.getAttribute('y1') === l.getAttribute('y2')) l.setAttribute('x2', w);
  });
  var band = svg.querySelector('rect');
  if (band) band.setAttribute('width', w);

  var set = function (sel, vals, min, max) {
    var el = svg.querySelector(sel);
    if (el) el.setAttribute('points', points(vals, w, h, min, max));
  };
  set('[data-line="hr"]', s.hr, 55, 100);
  set('[data-line="glu"]', s.glu, 80, 150);
  set('[data-line="bp"]', s.bp, 100, 135);

  var axis = svg.querySelector('.hw-axis');
  if (axis) axis.innerHTML = s.axis.map(function (label, i) {
    var x = ((i / (s.axis.length - 1)) * (w - 34)).toFixed(0);
    return '<text x="' + x + '" y="' + (svg.dataset.axisY || 200) + '">' + label + '</text>';
  }).join('');
  svg.dataset.chart = key;
}

function redrawAll() {
  document.querySelectorAll('[data-chart]').forEach(function (svg) { drawChart(svg, svg.dataset.chart); });
}
window.addEventListener('resize', redrawAll);

redrawAll();
