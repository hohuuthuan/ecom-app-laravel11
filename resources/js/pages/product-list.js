document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('product-filter-form');
  if (!form) { return; }

  var minRange = document.getElementById('priceRangeMin');
  var maxRange = document.getElementById('priceRangeMax');
  if (!minRange || !maxRange) { return; }

  var bubbleMin = document.getElementById('priceBubbleMin');
  var bubbleMax = document.getElementById('priceBubbleMax');
  var selectedTrack = document.getElementById('priceRangeSelected');

  var inputMin = form.querySelector('input[name="price_min"]');
  var inputMax = form.querySelector('input[name="price_max"]');
  if (!inputMin || !inputMax) { return; }

  var presetButtons = form.querySelectorAll('.price-preset-btn');

  function fmtVnd(n) {
    try { return new Intl.NumberFormat('vi-VN').format(n) + 'đ'; }
    catch { return String(n) + 'đ'; }
  }

  function clamp(val, min, max) {
    if (val < min) return min;
    if (val > max) return max;
    return val;
  }

  function updateUiFromRanges() {
    var minVal = parseInt(minRange.value || '0', 10);
    var maxVal = parseInt(maxRange.value || '0', 10);

    if (minVal > maxVal) {
      minVal = maxVal;
      minRange.value = String(minVal);
    }

    inputMin.value = String(minVal);
    inputMax.value = String(maxVal);

    if (bubbleMin) { bubbleMin.textContent = fmtVnd(minVal); }
    if (bubbleMax) { bubbleMax.textContent = fmtVnd(maxVal); }

    if (selectedTrack) {
      var max = parseInt(minRange.max || '0', 10);
      if (max > 0) {
        var left = (minVal / max) * 100;
        var right = 100 - (maxVal / max) * 100;
        selectedTrack.style.left = left + '%';
        selectedTrack.style.right = right + '%';
      }
    }

    presetButtons.forEach(function (btn) {
      var bMin = parseInt(btn.getAttribute('data-price-min') || '0', 10);
      var bMax = parseInt(btn.getAttribute('data-price-max') || '0', 10);
      btn.classList.toggle('is-active', bMin === minVal && bMax === maxVal);
    });
  }

  function setPriceAndSubmit(minVal, maxVal) {
    var max = parseInt(minRange.max || '0', 10);

    minVal = clamp(minVal, 0, max);
    maxVal = clamp(maxVal, 0, max);
    if (minVal > maxVal) { minVal = maxVal; }

    minRange.value = String(minVal);
    maxRange.value = String(maxVal);

    updateUiFromRanges();
    form.submit();
  }

  presetButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var minVal = parseInt(btn.getAttribute('data-price-min') || '0', 10);
      var maxVal = parseInt(btn.getAttribute('data-price-max') || '0', 10);
      setPriceAndSubmit(minVal, maxVal);
    });
  });

  minRange.addEventListener('input', updateUiFromRanges);
  maxRange.addEventListener('input', updateUiFromRanges);

  updateUiFromRanges();
});
