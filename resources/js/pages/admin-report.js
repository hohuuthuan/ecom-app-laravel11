(function () {
  "use strict";

  function scrollToLayout() {
    var layout = document.querySelector(".admin-report-layout");
    if (!layout) return;

    var top = layout.getBoundingClientRect().top + window.scrollY - 80;
    if (window.scrollY > top) {
      window.scrollTo({
        top: top,
        behavior: "smooth"
      });
    }
  }

  function initRevenueChart() {
    var chartContainer = document.getElementById("reportRevenueChart");
    if (!chartContainer) return;

    var dataElement = document.getElementById("reportRevenueData");
    if (!dataElement) return;

    var chartData = dataElement.getAttribute("data-chart");
    if (!chartData) return;

    try {
      var data = JSON.parse(chartData);
      var labels = Array.isArray(data.labels) ? data.labels : [];
      var revenue = Array.isArray(data.revenue) ? data.revenue : [];
      var cogs = Array.isArray(data.cogs) ? data.cogs : [];
      var profit = Array.isArray(data.profit) ? data.profit : [];

      if (labels.length === 0) return;

      var ctx = chartContainer.getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Doanh thu",
              data: revenue,
              backgroundColor: "#0d6efd",
              borderColor: "#0d6efd",
              borderWidth: 0,
              borderRadius: 4,
              yAxisID: "y"
            },
            {
              label: "Giá vốn",
              data: cogs,
              backgroundColor: "#ffc107",
              borderColor: "#ffc107",
              borderWidth: 0,
              borderRadius: 4,
              yAxisID: "y"
            },
            {
              label: "Lợi nhuận",
              data: profit,
              backgroundColor: "#198754",
              borderColor: "#198754",
              borderWidth: 0,
              borderRadius: 4,
              yAxisID: "y"
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          indexAxis: "x",
          interaction: {
            mode: "index",
            intersect: false
          },
          plugins: {
            legend: {
              display: true,
              position: "top",
              labels: {
                boxWidth: 12,
                padding: 15,
                font: {
                  size: 13,
                  weight: "500"
                },
                color: "#6c757d",
                usePointStyle: true,
                pointStyle: "circle"
              }
            },
            tooltip: {
              backgroundColor: "rgba(0, 0, 0, 0.8)",
              titleFont: { size: 13, weight: "bold" },
              bodyFont: { size: 12 },
              padding: 12,
              displayColors: true,
              callbacks: {
                label: function (context) {
                  var label = context.dataset.label || "";
                  if (label) label += ": ";
                  var value = context.parsed.y || 0;
                  return label + formatVND(value);
                }
              }
            }
          },
          scales: {
            x: {
              display: true,
              ticks: {
                font: { size: 11 },
                color: "#6c757d"
              },
              grid: {
                drawBorder: false,
                color: "rgba(0, 0, 0, 0.05)"
              }
            },
            y: {
              type: "linear",
              position: "left",
              ticks: {
                font: { size: 11 },
                color: "#6c757d",
                callback: function (value) {
                  return formatVNDShort(value);
                }
              },
              grid: {
                drawBorder: false,
                color: "rgba(0, 0, 0, 0.05)"
              }
            }
          }
        }
      });
    } catch (e) {
      console.error("Lỗi khởi tạo biểu đồ:", e);
    }
  }

  function formatVND(value) {
    var number = Number(value) || 0;
    if (number === 0) return "0đ";
    return new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    })
      .format(number)
      .replace("₫", "đ");
  }

  function formatVNDShort(value) {
    var number = Number(value) || 0;
    if (number === 0) return "0";
    if (number >= 1000000000) return (number / 1000000000).toFixed(1) + "B";
    if (number >= 1000000) return (number / 1000000).toFixed(1) + "M";
    if (number >= 1000) return (number / 1000).toFixed(0) + "K";
    return number.toString();
  }

  function setupClearButtons() {
    var clearButtons = document.querySelectorAll(".js-report-clear");
    clearButtons.forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        var form = btn.closest("form");
        if (!form) return;

        var inputs = form.querySelectorAll("input, select");
        inputs.forEach(function (input) {
          if (input.name === "tab") return;
          if (input.type === "checkbox" || input.type === "radio") {
            input.checked = false;
          } else {
            input.value = "";
          }
        });
        form.submit();
      });
    });
  }

  function setupTabs() {
    var navLinks = document.querySelectorAll(".admin-report-nav-link");
    navLinks.forEach(function (link) {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        var target = link.getAttribute("data-report-target");
        if (!target) return;

        var sections = document.querySelectorAll(".report-section");
        sections.forEach(function (section) {
          section.classList.remove("active");
        });

        var navItems = document.querySelectorAll(".admin-report-nav-link");
        navItems.forEach(function (item) {
          item.classList.remove("active");
        });

        var targetSection = document.querySelector(
          '[data-report-section="' + target + '"]'
        );
        if (targetSection) {
          targetSection.classList.add("active");
        }

        link.classList.add("active");

        var url = new URL(window.location);
        url.searchParams.set("tab", target);
        window.history.pushState({}, "", url);

        setTimeout(function () {
          var layout = document.querySelector(".admin-report-layout");
          if (!layout) return;
          var top = layout.getBoundingClientRect().top + window.scrollY - 80;
          window.scrollTo({
            top: top,
            behavior: "smooth"
          });
        }, 100);
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    scrollToLayout();
    initRevenueChart();
    setupClearButtons();
    setupTabs();
  });
})();
