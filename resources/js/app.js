import "./bootstrap";
import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);
window.Chart = Chart;

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
