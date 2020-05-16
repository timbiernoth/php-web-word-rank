<style>

    *,
    *:before,
    *:after {
        box-sizing: border-box;
    }

    html,
    body,
    input,
    select,
    option,
    button {
        font-family: 'Work Sans', 'Arial', 'Helvetica', sans-serif;
        font-size: 14px;
    }

    /*
    input,
    select,
    option,
    button {
       -webkit-appearance: none;
       -moz-appearance: none;
       appearance: none;
       -webkit-border-radius: 0;
       -moz-border-radius: 0;
       border-radius: 0;
    }
    */

    b,
    strong {
        font-family: 'Work Sans', 'Arial', 'Helvetica', sans-serif;
        font-weight: 600;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
    }

    a {
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    h1 {
        margin: 5px 0 0 0;
        padding: 0;
        font-size: 26px;
        font-family: 'Gloria Hallelujah';
    }

    h1 a {
        color: #000;
    }

    h2,
    h3,
    h4,
    h5,
    h6,
    table thead,
    .sidebar li strong,
    .sidebar label {
        font-family: 'News Cycle';
        font-weight: bold;
    }

    h2 {
        font-size: 20px;
        margin: 0;
    }

    h3 {
        font-size: 16px;
        margin: 0 0 10px 0;
    }

    p {
        text-align: justify;
    }

    hr {
        margin: 10px 0;
        display: block;
        height: 1px;
        border: 0;
        border-top: 1px solid #ccc;
        padding: 0;
    }

    table td,
    table th {
        padding: 2px 4px;
    }

    button,
    select,
    option,
    .sidebar label {
        cursor: pointer;
    }

    .text-align-left {
        text-align: left !important;
    }

    .text-align-center {
        text-align: center !important;
    }

    .white-space-nowrap {
        white-space: nowrap !important;
    }

    .sidebar {
        position: fixed;
        width: 240px;
        left: 0;
        top: 0;
        height: 100%;
        background: #eee;
        box-shadow: 0 0 10px 0 rgba(0,0,0,0.4);
    }

    .content {
        width: calc(100vw - 280px);
        max-width: calc(100vw - 280px);
        margin-left: 250px;
    }

    .sidebar ul,
    .sidebar ul li {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .sidebar ul + ul {
        margin-top: 10px;
    }

    hr,
    .wordrank-table,
    .wordrank-compare-table,
    .form-wordrank-input,
    .quality-issues-table,
    .compare-issues-table,
    .wordrank-compare-statistics-table {
        display: block;
        float: left;
        width: 100%;
    }

    .wordrank-table,
    .wordrank-compare-table,
    .quality-issues-table,
    .compare-issues-table,
    .wordrank-compare-statistics-table {
        display: table;
    }

    .wordrank-table .to-toggle {
        cursor: pointer;
    }

    .wordrank-table .toggle.hide {
        display: none;
    }

    .wordrank-table tr td:last-child span,
    .wordrank-compare-table tr td:last-child span {
        display: inherit;
        width: 2px;
        height: 4px;
        background: #000;
    }

    .wordrank-table tr td span + span,
    .wordrank-compare-table tr td span + span {
        margin-left: 1px;
    }

    .wordrank-compare-statistics-table tbody tr td + td {
        text-align: center;
    }

    .wordrank-compare-statistics-table tbody tr:first-child td {
        vertical-align: bottom;
        text-align: center;
    }

    .wordrank-compare-statistics-table tbody tr:first-child td span {
        display: block;
        width: 10px;
        height: 1px;
        background: #000;
        margin: 0 auto;
    }

    .wordrank-compare-statistics-table tbody tr:first-child td span + span {
        margin-top: 1px;
    }

    .wordrank-compare-statistics-table tbody tr:first-child td span

    .wordrank-table th,
    .wordrank-table td,
    .wordrank-compare-table th,
    .wordrank-compare-table td {
        text-align: left;
        vertical-align: middle;
    }

    table th,
    .wordrank-table th,
    .wordrank-compare-table th {
        vertical-align: top;
    }

    .wordrank-table tr td:nth-child(2),
    .wordrank-compare-table tr td:nth-child(2) {
        min-width: 240px;
    }

    .stats-table tbody tr:nth-child(odd) td,
    .debug-table tbody tr:nth-child(odd) td,
    .wordrank-table tbody tr:nth-child(odd) td,
    .wordrank-compare-table tbody tr:nth-child(odd) td,
    .quality-issues-table tbody tr:nth-child(odd) td,
    .quality-issues-results tbody tr:nth-child(odd) td,
    .compare-issues-table tbody tr:nth-child(odd) td,
    .wordrank-compare-statistics-table tbody tr:nth-child(odd) td,
    .quality-issues-table tfoot td {
        background: #ededed;
    }

    .quality-issues-table tfoot tr th + th {
        font-weight: normal;
        font-style: italic;
    }

    .input-url {
        width: calc(100% - 10px);
        border: 1px solid #ddd;
        padding: 2px 4px;
    }

    .form-wordrank-input + .form-wordrank-input {
        margin-top: 5px;
    }

    .form-wordrank {
        float: left;
        text-align: center;
        padding: 0 10px;
    }

    .form-wordrank-widget {
        width: calc(50% - 10px);
        float: left;
        margin: 0 5px;
    }

    .form-wordrank-widget label + select {
        margin-top: 5px;
    }

    .form-wordrank-widget select {
        width: 100%;
    }

    .form-wordrank button {
        width: 50%;
    }

    .title-spam {
        background: red !important;
        color: white !important;
    }

    .quality-issue-critical,
    .quality-issue-high,
    .quality-issue-medium,
    .quality-issue-low,
    .quality-issue-good {
        padding: 0 3px;
    }

    .quality-issue-critical {
        background: #8B0000 !important;
        color: #FFF !important;
    }

    .quality-issue-high {
        background: #FF0000 !important;
        color: #FFF !important;
    }

    .quality-issue-medium {
        background: #FFA500 !important;
        color: #000 !important;
    }

    .quality-issue-low {
        background: #FFFF00 !important;
        color: #000 !important;
    }

    .quality-issue-good {
        background: #c2d9a5 !important;
        color: #000 !important;
    }

    /* DARK */

    body.dark {
        background: #010101;
        color: #fbfbfb;
    }

    body.dark a {
        color: #aaa;
    }

    body.dark hr {
        border-top: 1px solid #333;
    }

    body.dark h1,
    body.dark h1 a {
        color: #666;
    }

    body.dark .sidebar {
        background: #191919;
        box-shadow: 0 0 20px 0 rgba(255,255,255,0);
        border-right: 1px solid #333;
    }

    body.dark .sidebar h1,
    body.dark .sidebar li strong,
    body.dark .sidebar li a,
    body.dark .sidebar label {
        text-shadow: -1px -1px 0 #000;
    }

    body.dark .sidebar input,
    body.dark .sidebar select,
    body.dark .sidebar option {
        background: #010101;
        color: #ccc;
        border-color: #333;
    }

    body.dark .sidebar input:hover,
    body.dark .sidebar select:hover,
    body.dark .sidebar option:hover {
        border-color: #666;
    }

    body.dark .sidebar input:active,
    body.dark .sidebar select:active,
    body.dark .sidebar option:active,
    body.dark .sidebar input:focus,
    body.dark .sidebar select:focus,
    body.dark .sidebar option:focus {
        border-color: #999;
    }

    body.dark .sidebar button {
        background: #666;
        border: 1px solid #333;
        color: #fff;
        box-shadow: 0 0 12px 0 rgba(0,0,0,0.6);
    }

    body.dark .sidebar button:hover {
        box-shadow: 0 0 6px 0 rgba(0,0,0,0.6);
    }

    body.dark .sidebar button:active,
    body.dark .sidebar button:focus {
        box-shadow: 0 0 0 0 rgba(0,0,0,0.6);
    }

    body.dark .stats-table tbody tr:nth-child(2n+1) td,
    body.dark .debug-table tbody tr:nth-child(2n+1) td,
    body.dark .wordrank-table tbody tr:nth-child(2n+1) td,
    body.dark .wordrank-compare-table tbody tr:nth-child(2n+1) td,
    body.dark .quality-issues-table tbody tr:nth-child(2n+1) td,
    body.dark .quality-issues-results tbody tr:nth-child(2n+1) td,
    body.dark .compare-issues-table tbody tr:nth-child(2n+1) td,
    body.dark .wordrank-compare-statistics-table tbody tr:nth-child(2n+1) td,
    body.dark .quality-issues-table tfoot td {
        background: #222;
    }

    body.dark .wordrank-table tr td:last-child span,
    body.dark .wordrank-compare-table tr td:last-child span,
    body.dark .wordrank-compare-statistics-table tbody tr:first-child td span,
    body.dark table thead,
    body.dark table tfoot {
        background: #666;
    }

    body.dark .sidebar li strong,
    body.dark .sidebar label {
        color: #666;
    }

    body.dark table thead,
    body.dark table tfoot {
        color: #ccc;
    }

    body.dark .sidebar input[type="checkbox"] {
        opacity: 0.6;
    }

    body.dark .sidebar option,
    body.dark .sidebar select {
        border: 1px solid #333;
    }

</style>
