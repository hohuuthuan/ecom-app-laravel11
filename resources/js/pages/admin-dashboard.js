(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var data = getDashboardData();

    renderTopProducts(data);
    renderLowStockProducts(data);
    renderOrderStatusChart(data);
    renderTopCustomersChart(data);
    renderRevenueChart(data);
  });

  function getDashboardData() {
    var raw = window.ADMIN_DASHBOARD_DATA;
    if (!raw || typeof raw !== 'object') {
      return {};
    }
    return raw;
  }

  function formatVnd(value) {
    var number = Number(value) || 0;

    if (typeof Intl !== 'undefined' && Intl.NumberFormat) {
      return new Intl.NumberFormat('vi-VN').format(number) + ' đ';
    }

    return number.toLocaleString('vi-VN') + ' đ';
  }

  function safeArray(value) {
    return Array.isArray(value) ? value : [];
  }

  /* ================= TOP PRODUCTS ================= */

  function renderTopProducts(data) {
    var container = document.getElementById('topProductsList');
    if (!container) {
      return;
    }

    var items = data.top_products || data.topProducts;
    items = safeArray(items);

    if (!items.length) {
      container.innerHTML = '<div class="text-muted mini">Chưa có dữ liệu sản phẩm bán chạy.</div>';
      return;
    }

    var html = '';
    for (var i = 0; i < items.length; i++) {
      var item = items[i] || {};

      var name = item.product_name || item.name || 'Sản phẩm';
      var sku = item.sku || item.code || null;
      var sold = item.total_sold || item.total_qty || item.quantity || 0;
      var revenue = item.revenue_vnd || item.revenue || 0;

      html +=
        '<div class="admin-product-item">' +
          '<div class="admin-product-main">' +
            '<div class="admin-product-rank">#' + (i + 1) + '</div>' +
            '<div class="admin-product-details">' +
              '<div class="admin-product-name">' + escapeHtml(name) + '</div>' +
              '<div class="admin-product-meta">' +
                '<span class="admin-product-meta-item">Đã bán: ' + sold + ' sp</span>';

      if (sku) {
        html +=
          '<span class="admin-product-meta-dot">•</span>' +
          '<span class="admin-product-meta-item">Mã: ' + escapeHtml(String(sku)) + '</span>';
      }

      html +=
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="admin-product-badge">' +
            '<span>Doanh thu</span>' +
            '<strong>' + formatVnd(revenue) + '</strong>' +
          '</div>' +
        '</div>';
    }

    container.innerHTML = html;
  }

  /* ============= LOW STOCK PRODUCTS ============= */

  function renderLowStockProducts(data) {
    var container = document.getElementById('outOfStockList');
    if (!container) {
      return;
    }

    var items =
      data.low_stock_products ||
      data.lowStockProducts ||
      data.out_of_stock ||
      [];
    items = safeArray(items);

    if (!items.length) {
      container.innerHTML = '<div class="text-muted mini">Không có sản phẩm sắp hết hàng.</div>';
      return;
    }

    var html = '';
    for (var i = 0; i < items.length; i++) {
      var item = items[i] || {};

      var name = item.product_name || item.name || 'Sản phẩm';
      var sku = item.sku || item.code || null;
      var onHand = item.on_hand || item.quantity || 0;
      var threshold = item.safe_stock || item.min_stock || 0;
      var warehouse = item.warehouse_name || item.warehouse || null;

      html +=
        '<div class="admin-product-item admin-product-item--warning">' +
          '<div class="admin-product-main">' +
            '<div class="admin-product-rank">!</div>' +
            '<div class="admin-product-details">' +
              '<div class="admin-product-name">' + escapeHtml(name) + '</div>' +
              '<div class="admin-product-meta">' +
                '<span class="admin-product-meta-item">Tồn kho: ' + onHand + '</span>';

      if (threshold) {
        html +=
          '<span class="admin-product-meta-dot">•</span>' +
          '<span class="admin-product-meta-item">Ngưỡng cảnh báo: ' + threshold + '</span>';
      }

      if (warehouse) {
        html +=
          '<span class="admin-product-meta-dot">•</span>' +
          '<span class="admin-product-meta-item">Kho: ' + escapeHtml(String(warehouse)) + '</span>';
      }

      if (sku) {
        html +=
          '<span class="admin-product-meta-dot">•</span>' +
          '<span class="admin-product-meta-item">Mã: ' + escapeHtml(String(sku)) + '</span>';
      }

      html +=
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="admin-product-badge admin-product-badge--danger">' +
            '<span>Cảnh báo</span>' +
            '<strong>Sắp hết hàng</strong>' +
          '</div>' +
        '</div>';
    }

    container.innerHTML = html;
  }

  /* ================= ORDER STATUS CHART ================= */

  function renderOrderStatusChart(data) {
    var canvas = document.getElementById('orderStatusChart');
    if (!canvas || typeof Chart === 'undefined') {
      return;
    }

    var source =
      data.order_status_chart ||
      data.orderStatusChart ||
      data.order_status_distribution ||
      {};

    var labels = safeArray(source.labels);
    var values = safeArray(source.data || source.values);

    if (!labels.length || !values.length) {
      labels = ['Hoàn tất', 'Đang giao', 'Thất bại', 'Hoàn / trả'];
      values = [60, 25, 10, 5];
    }

    // Map màu theo label:
    // - Hoàn tất / thành công: xanh lá (#22c55e)
    // - Đang giao: vàng đất (#f59e0b)
    // - Thất bại: đỏ (#ef4444)
    // - Hoàn / trả: cam (#fb923c)
    var bgColors = [];
    for (var i = 0; i < labels.length; i++) {
      var text = String(labels[i] || '').toLowerCase();
      var color = '#0ea5e9';

      if (
        text.indexOf('hoàn tất') !== -1 ||
        text.indexOf('thành công') !== -1 ||
        text.indexOf('completed') !== -1 ||
        text.indexOf('delivered') !== -1
      ) {
        color = '#22c55e';
      } else if (
        text.indexOf('đang giao') !== -1 ||
        text.indexOf('shipping') !== -1 ||
        text.indexOf('shipped') !== -1
      ) {
        color = '#f59e0b';
      } else if (
        text.indexOf('thất bại') !== -1 ||
        text.indexOf('failed') !== -1
      ) {
        color = '#ef4444';
      } else if (
        text.indexOf('hoàn / trả') !== -1 ||
        text.indexOf('hoàn') !== -1 ||
        text.indexOf('trả') !== -1 ||
        text.indexOf('returned') !== -1
      ) {
        color = '#fb923c';
      }

      bgColors.push(color);
    }

    var ctx = canvas.getContext('2d');

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [
          {
            data: values,
            backgroundColor: bgColors,
            borderWidth: 0,
            hoverOffset: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              boxWidth: 18,
              boxHeight: 18
            }
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                var label = context.label || '';
                var value = context.parsed || 0;
                var total = 0;
                var dataset = context.chart.data.datasets[0].data;
                for (var j = 0; j < dataset.length; j++) {
                  total += dataset[j];
                }
                var percent = total ? (value * 100) / total : 0;
                return label + ': ' + value + ' đơn (' + percent.toFixed(1) + '%)';
              }
            }
          }
        },
        cutout: '65%'
      }
    });
  }

  /* ================= TOP CUSTOMERS CHART ================= */

  function renderTopCustomersChart(data) {
    var canvas = document.getElementById('topCustomerChart');
    if (!canvas || typeof Chart === 'undefined') {
      return;
    }

    var customers = data.top_customers || data.topCustomers;
    customers = safeArray(customers);

    if (!customers.length) {
      customers = [
        { name: 'Khách 1', total_spent_vnd: 5000000 },
        { name: 'Khách 2', total_spent_vnd: 4200000 },
        { name: 'Khách 3', total_spent_vnd: 3500000 },
        { name: 'Khách 4', total_spent_vnd: 3100000 },
        { name: 'Khách 5', total_spent_vnd: 2800000 }
      ];
    }

    var labels = [];
    var values = [];
    for (var i = 0; i < customers.length; i++) {
      var c = customers[i] || {};
      labels.push(c.name || 'Khách ' + (i + 1));
      values.push(c.total_spent_vnd || c.total_spent || 0);
    }

    var ctx = canvas.getContext('2d');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Tổng chi tiêu (VNĐ)',
            data: values,
            backgroundColor: '#4f46e5',
            borderRadius: 6,
            maxBarThickness: 44
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return formatVnd(context.parsed.x || 0);
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return formatShortVnd(value);
              }
            },
            grid: {
              display: false
            }
          },
          y: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  /* ================= REVENUE CHART (BAR) ================= */

  function renderRevenueChart(data) {
    var canvas = document.getElementById('revenueChart');
    if (!canvas || typeof Chart === 'undefined') {
      return;
    }

    var source =
      data.revenue_series ||
      data.revenueSeries ||
      data.monthly_revenue ||
      {};

    var labels = safeArray(source.labels);
    var revenue = safeArray(source.revenue);
    var cogs = safeArray(source.cogs);
    var profit = safeArray(source.profit);

    if (!labels.length || !revenue.length) {
      labels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
      revenue = [12, 14, 11, 16, 18, 20, 22, 23, 19, 21, 24, 26];
      cogs = [8, 9, 8, 10, 11, 13, 14, 15, 13, 14, 15, 16];
      profit = [4, 5, 3, 6, 7, 7, 8, 8, 6, 7, 9, 10];

      var factor = 1000000;
      for (var i = 0; i < labels.length; i++) {
        revenue[i] = revenue[i] * factor;
        cogs[i] = cogs[i] * factor;
        profit[i] = profit[i] * factor;
      }
    }

    var ctx = canvas.getContext('2d');

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Doanh thu',
            data: revenue,
            backgroundColor: '#4f46e5',
            borderRadius: 4,
            maxBarThickness: 40
          },
          {
            label: 'Giá vốn',
            data: cogs,
            backgroundColor: '#f97316',
            borderRadius: 4,
            maxBarThickness: 40
          },
          {
            label: 'Lợi nhuận',
            data: profit,
            backgroundColor: '#22c55e',
            borderRadius: 4,
            maxBarThickness: 40
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              usePointStyle: true,
              pointStyle: 'circle',
              padding: 12
            }
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                var label = context.dataset.label || '';
                var value = context.parsed.y || 0;
                return label + ': ' + formatVnd(value);
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0,0,0,0.05)',
              drawBorder: false
            },
            ticks: {
              callback: function (value) {
                return formatShortVnd(value);
              }
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  /* ================= HELPERS ================= */

  function formatShortVnd(value) {
    var num = Number(value) || 0;
    var abs = Math.abs(num);

    if (abs >= 1000000000) {
      return (num / 1000000000).toFixed(1).replace(/\.0$/, '') + ' tỷ';
    }
    if (abs >= 1000000) {
      return (num / 1000000).toFixed(1).replace(/\.0$/, '') + ' tr';
    }
    if (abs >= 1000) {
      return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    }
    return num.toString();
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
})();
