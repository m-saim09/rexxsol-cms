<style>
    .rex-nav-inline-page {
        color: #172033;
        max-width: 1440px;
        margin: 0 auto;
        display: grid;
        gap: 20px;
        padding-bottom: 24px;
    }
    .rex-nav-inline-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid #eef1f6;
        padding-bottom: 16px;
    }
    .rex-nav-inline-header p {
        margin: 0;
        color: #2563eb;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }
    .rex-nav-inline-header h1 {
        margin: 6px 0 0;
        color: #172033;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.1;
    }
    .rex-nav-inline-header .rex-nav-inline-subtext {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }
    .rex-nav-workspace {
        border: 1px solid #e5eaf2;
        border-radius: 12px;
        background: #fff;
        padding: 16px;
        box-shadow: 0 2px 12px rgba(23,32,51,.04);
    }
    .rex-nav-workspace-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; flex-wrap: wrap; }
    .rex-nav-workspace-label { margin: 0; color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .rex-nav-workspace-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .rex-nav-create-menu { display: flex; align-items: center; gap: 8px; }
    .rex-nav-create-menu .cms-input { width: 170px; }
    .rex-nav-tabs { display: inline-flex; width: fit-content; gap: 6px; padding: 4px; border: 1px solid #e5eaf2; border-radius: 10px; background: #f7f9fc; max-width: 100%; overflow-x: auto; }
    .rex-nav-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 8px 14px;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        background: transparent;
        cursor: pointer;
        transition: all .15s ease;
    }
    .rex-nav-tab:hover { color: #172033; background: rgba(255,255,255,0.7); }
    .rex-nav-tab--active {
        border-color: rgba(37,99,235,.2);
        background: #fff;
        color: #1d4ed8;
        box-shadow: 0 1px 3px rgba(37,99,235,.08);
    }
    .rex-nav-tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        padding: 2px 7px;
        border-radius: 999px;
        background: rgba(148,163,184,.16);
        font-size: 11px;
        font-weight: 700;
    }
    .rex-nav-tab--active .rex-nav-tab-count {
        background: #e0edff;
        color: #1d4ed8;
    }
    .rex-footer-subnav {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #f1f4f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .rex-footer-subnav-group {
        display: inline-flex;
        gap: 4px;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 8px;
    }
    .rex-footer-subnav-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all .15s ease;
    }
    .rex-footer-subnav-btn:hover { color: #172033; }
    .rex-footer-subnav-btn--active {
        background: #fff;
        color: #0f172a;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
    .rex-nav-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) minmax(320px, .85fr);
        gap: 20px;
        align-items: start;
    }
    .rex-nav-list-panel, .rex-nav-editor {
        border: 1px solid #e5eaf2;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(23,32,51,.04);
    }
    .rex-nav-list-panel { padding: 20px; }
    .rex-nav-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }
    .rex-nav-panel-title { margin: 0; color: #172033; font-size: 16px; font-weight: 700; }
    .rex-nav-panel-copy { margin: 4px 0 0; color: #64748b; font-size: 12px; }
    .rex-nav-count {
        display: inline-flex;
        align-items: center;
        border: 1px solid #e5eaf2;
        border-radius: 999px;
        background: #f7f9fc;
        padding: 5px 10px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }
    .rex-footer-col {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        margin-bottom: 14px;
        overflow: hidden;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .rex-footer-col:hover { border-color: #cbd5e1; }
    .rex-footer-col--disabled { opacity: 0.72; background: #fafbfc; }
    .rex-footer-col-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f7;
    }
    .rex-footer-col-title-area {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }
    .rex-drag-handle {
        cursor: grab;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        padding: 2px;
        border-radius: 4px;
    }
    .rex-drag-handle:hover { color: #475569; background: #e2e8f0; }
    .rex-footer-col-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .rex-footer-col-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .rex-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        transition: all .15s ease;
    }
    .rex-icon-btn:hover { border-color: #93c5fd; color: #2563eb; background: #f8faff; }
    .rex-icon-btn--danger:hover { border-color: #fca5a5; color: #dc2626; background: #fef2f2; }
    .rex-footer-col-body {
        padding: 12px;
        display: grid;
        gap: 8px;
    }
    .rex-link-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #eef2f7;
        border-radius: 8px;
        background: #fff;
        transition: all .15s ease;
    }
    .rex-link-item:hover { border-color: #dbeafe; background: #fbfdff; }
    .rex-link-item--active { border-color: #93c5fd; background: #f0f7ff; }
    .rex-link-item--disabled { opacity: 0.65; background: #f8fafc; }
    .rex-link-item-main {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }
    .rex-link-item-info { min-width: 0; flex: 1; }
    .rex-link-item-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .rex-link-item-title {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }
    .rex-link-item-meta {
        font-size: 11px;
        color: #64748b;
        margin: 2px 0 0;
        font-family: monospace;
        word-break: break-all;
    }
    .rex-link-item-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .rex-badge {
        display: inline-flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #f8fafc;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
    }
    .rex-badge--success { border-color: rgba(34,197,94,.2); background: #f0fdf4; color: #166534; }
    .rex-badge--warning { border-color: rgba(234,179,8,.3); background: #fefce8; color: #854d0e; }
    .rex-badge--muted { border-color: #e2e8f0; background: #f1f5f9; color: #64748b; }
    .rex-nav-list { display: grid; gap: 12px; }
    .rex-nav-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid #e5eaf2;
        border-radius: 10px;
        background: #fff;
        padding: 14px 14px 12px;
    }
    .rex-nav-item:hover { border-color: #dbeafe; background: #fbfdff; }
    .rex-nav-item--active { border-color: rgba(37,99,235,.35); background: #f6faff; }
    .rex-nav-main { display: flex; flex: 1; min-width: 0; gap: 12px; }
    .rex-nav-order-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #eef4ff;
        color: #2563eb;
        font-size: 11px;
        font-weight: 700;
    }
    .rex-nav-content { min-width: 0; flex: 1; }
    .rex-nav-heading-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
    .rex-nav-content h3 { margin: 0; color: #172033; font-size: 15px; font-weight: 700; }
    .rex-nav-badges { display: inline-flex; flex-wrap: wrap; justify-content: flex-end; gap: 6px; }
    .rex-nav-badge {
        display: inline-flex;
        align-items: center;
        border: 1px solid #e5eaf2;
        border-radius: 999px;
        background: #f8fafc;
        padding: 4px 8px;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
    }
    .rex-nav-badge--success { border-color: rgba(34,197,94,.2); background: rgba(34,197,94,.1); color: #166534; }
    .rex-nav-badge--muted { border-color: rgba(148,163,184,.25); background: rgba(148,163,184,.09); color: #475569; }
    .rex-nav-meta { margin: 8px 0 0; color: #64748b; font-size: 12px; line-height: 1.45; word-break: break-word; }
    .rex-nav-nested { margin-top: 10px; border-left: 2px solid #dbeafe; padding-left: 12px; }
    .rex-nav-nested-item { display: flex; align-items: center; gap: 10px; padding: 6px 0; }
    .rex-nav-nested-line { display: inline-block; width: 8px; height: 1px; background: #bfd7ff; }
    .rex-nav-nested-details {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        border-radius: 6px;
        background: #f8fbff;
        padding: 5px 8px;
        color: #334155;
        font-size: 12px;
    }
    .rex-nav-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
    .rex-nav-order-actions { display: flex; gap: 6px; }
    .rex-nav-order-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 1px solid #e5eaf2;
        border-radius: 7px;
        background: #fff;
        color: #475569;
    }
    .rex-nav-order-button:hover { border-color: #bfdbfe; color: #1d4ed8; background: #f8fbff; }
    .rex-nav-edit-actions { display: flex; align-items: center; gap: 8px; }
    .rex-nav-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 7px 13px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }
    .rex-nav-action-button--secondary { border: 1px solid #dbe2ea; background: #fff; color: #172033; }
    .rex-nav-action-button--secondary:hover { border-color: #93c5fd; color: #1d4ed8; background: #f8fbff; }
    .rex-nav-action-button--primary { border: 1px solid #2563eb; background: #2563eb; color: #fff; }
    .rex-nav-action-button--primary:hover { background: #1d4ed8; }
    .rex-nav-action-button--danger { border: 1px solid transparent; background: transparent; color: #b91c1c; }
    .rex-nav-action-button--danger:hover { background: rgba(220,38,38,.04); }
    .rex-nav-editor { position: sticky; top: 1.5rem; padding: 20px; }
    .rex-nav-editor-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 14px;
        border-bottom: 1px solid #eef1f6;
    }
    .rex-nav-editor-kicker { margin: 0; color: #2563eb; font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .rex-nav-editor-head h2 { margin: 6px 0 0; color: #172033; font-size: 17px; font-weight: 700; }
    .rex-nav-editor-head p { margin: 4px 0 0; color: #64748b; font-size: 12px; }
    .rex-nav-editor-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 9px;
        background: #eef4ff;
        color: #2563eb;
    }
    .rex-nav-form { display: grid; gap: 15px; padding-top: 16px; }
    .rex-nav-field { display: grid; gap: 6px; }
    .rex-nav-field label { margin: 0; font-size: 13px; font-weight: 600; color: #334155; }
    .rex-nav-field input, .rex-nav-field select, .rex-nav-field textarea {
        width: 100%;
        border: 1px solid #dce4ef;
        border-radius: 8px;
        background: #fff;
        padding: 9px 12px;
        color: #172033;
        font-size: 13px;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .rex-nav-field input:focus, .rex-nav-field select:focus, .rex-nav-field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.1);
    }
    .rex-nav-two-up { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: end; }
    .rex-nav-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 1px solid #e5eaf2;
        border-radius: 8px;
        background: #f8fafc;
        padding: 8px 12px;
    }
    .rex-nav-toggle-help { margin: 2px 0 0; color: #64748b; font-size: 11px; }
    .rex-nav-toggle { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
    .rex-nav-toggle input { position: absolute; opacity: 0; pointer-events: none; }
    .rex-nav-toggle-slider {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 22px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: all .2s ease;
    }
    .rex-nav-toggle-slider::before {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15,23,42,.18);
        transition: all .2s ease;
    }
    .rex-nav-toggle input:checked + .rex-nav-toggle-slider { background: #2563eb; }
    .rex-nav-toggle input:checked + .rex-nav-toggle-slider::before { transform: translateX(16px); }
    .rex-nav-actions-bar { display: flex; justify-content: flex-end; gap: 10px; padding-top: 6px; }
    .rex-settings-grid { display: grid; gap: 20px; }
    .rex-settings-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .rex-settings-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 14px;
        margin-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .rex-settings-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .rex-settings-card-desc {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0;
    }
    .rex-settings-card-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }
    .rex-locations-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        padding: 20px;
    }
    .rex-locations-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 14px;
    }
    .rex-locations-table th {
        text-align: left;
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .rex-locations-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .rex-preview-card {
        border: 1px solid #dbeafe;
        border-radius: 12px;
        background: #f8fbff;
        padding: 20px;
        margin-bottom: 20px;
    }
    .rex-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .rex-preview-mockup {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .rex-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.45);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 16px;
    }
    .rex-modal-card {
        background: #fff;
        border-radius: 12px;
        width: 100%;
        max-width: 480px;
        padding: 24px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    }
    .rex-modal-title { font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
    .rex-modal-copy { font-size: 13px; color: #64748b; margin: 0 0 16px; line-height: 1.5; }
    .rex-empty-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 36px 20px;
        text-align: center;
        background: #fbfcfe;
    }
    .rex-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        border-radius: 10px;
        background: #eef4ff;
        color: #2563eb;
    }
    .rex-nav-inline-page .cms-error { margin-top: 4px; color: #dc2626; font-size: 12px; }
    @media (max-width: 960px) {
        .rex-nav-layout { grid-template-columns: minmax(0, 1fr); }
        .rex-nav-editor { position: static; }
    }

    .rex-nav-inline-page { max-width: 1440px; gap: 16px; }
    .rex-nav-inline-header { border: 0; padding: 0; }
    .rex-nav-inline-header p { letter-spacing: normal; text-transform: none; }
    .rex-nav-inline-header h1 { margin-top: 8px; font-size: 24px; letter-spacing: -0.025em; }
    .rex-nav-inline-header .rex-nav-inline-subtext { margin-top: 4px; font-size: 14px; }
    .rex-nav-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }
    .rex-nav-breadcrumb a { color: #206bc4; transition: color .15s ease; }
    .rex-nav-breadcrumb a:hover { color: #1a569d; text-decoration: underline; }
    .rex-nav-breadcrumb [aria-current="page"] { color: #64748b; }
    .rex-nav-workspace {
        padding: 0;
        border-color: #e5eaf2;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .rex-nav-workspace-header { margin: 0; padding: 12px 16px; }
    .rex-nav-workspace-actions { gap: 8px; }
    .rex-nav-toolbar { width: 100%; }
    .rex-nav-toolbar-count {
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        border-left: 1px solid #eef1f6;
        padding: 0 8px 0 14px;
        color: #64748b;
        font-size: 12px;
        white-space: nowrap;
    }
    .rex-nav-save-status {
        margin-left: auto;
        color: #64748b;
        font-size: 12px;
        white-space: nowrap;
    }
    .rex-nav-save-status.is-dirty { color: #b45309; font-weight: 600; }
    .rex-nav-menu-select { display: grid; gap: 4px; min-width: 180px; }
    .rex-nav-menu-select select {
        height: 40px;
        min-width: 180px;
        border: 1px solid #dbe2ed;
        border-radius: 6px;
        background: #fff;
        padding: 0 34px 0 11px;
        color: #172033;
        font-size: 13px;
        outline: none;
    }
    .rex-nav-menu-select select:focus,
    .rex-nav-inline-page input:focus-visible,
    .rex-nav-inline-page select:focus-visible,
    .rex-nav-inline-page button:focus-visible {
        outline: 2px solid rgba(32,107,196,.28);
        outline-offset: 2px;
    }
    .rex-nav-workspace-label { font-size: 10px; letter-spacing: .1em; }
    .rex-nav-action-button {
        min-height: 36px;
        border-radius: 6px;
        padding: 7px 12px;
        font-size: 13px;
        font-weight: 600;
    }
    .rex-nav-action-button--primary { border-color: #206bc4; background: #206bc4; }
    .rex-nav-action-button--primary:hover { background: #1a569d; }
    .rex-nav-action-button--secondary { border-color: #dbe2ed; color: #172033; }
    .rex-nav-create-menu { flex-wrap: wrap; }
    .rex-nav-create-menu .cms-input { width: 168px; height: 36px; border-radius: 6px; font-size: 13px; }
    .rex-nav-tabs { border-radius: 6px; }
    .rex-nav-tab { border-radius: 5px; }
    .rex-nav-layout:not(.rex-footer-layout) {
        grid-template-columns: minmax(260px, .48fr) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }
    .rex-footer-layout { grid-template-columns: minmax(0, 1.8fr) minmax(320px, .85fr); gap: 16px; }
    .rex-nav-add-panel,
    .rex-nav-list-panel,
    .rex-nav-editor {
        min-width: 0;
        border-color: #e5eaf2;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .rex-nav-add-panel,
    .rex-nav-list-panel { padding: 20px; }
    .rex-nav-structure-column { display: grid; min-width: 0; gap: 16px; }
    .rex-nav-panel-head { margin-bottom: 14px; }
    .rex-nav-panel-title { font-size: 16px; }
    .rex-nav-panel-copy { font-size: 13px; }
    .rex-nav-add-group {
        border: 1px solid #e5eaf2;
        border-radius: 6px;
        background: #fff;
    }
    .rex-nav-add-group + .rex-nav-add-group { margin-top: 10px; }
    .rex-nav-add-group > summary {
        display: flex;
        min-height: 44px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        color: #172033;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        list-style: none;
    }
    .rex-nav-add-group > summary::-webkit-details-marker { display: none; }
    .rex-nav-add-group > summary::after { content: '+'; color: #64748b; font-size: 17px; line-height: 1; }
    .rex-nav-add-group[open] > summary::after { content: '−'; }
    .rex-nav-add-group[open] > summary { border-bottom: 1px solid #eef1f6; }
    .rex-nav-add-group-body { display: grid; gap: 12px; padding: 12px; }
    .rex-nav-content-picker { margin: 0; padding: 0; border: 0; background: transparent; }
    .rex-nav-content-picker-head { margin: 0 0 12px; }
    .rex-nav-content-picker-head h3 { color: #172033; font-size: 13px; font-weight: 600; }
    .rex-nav-content-picker-head p { color: #64748b; font-size: 12px; }
    .rex-nav-content-picker-controls { display: grid; gap: 10px; }
    .rex-nav-content-picker-controls label { display: grid; gap: 5px; }
    .rex-nav-content-picker-controls label > span {
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }
    .rex-nav-content-picker-controls select,
    .rex-nav-content-picker-controls input {
        width: 100%;
        height: 40px;
        border: 1px solid #dbe2ed;
        border-radius: 6px;
        background: #fff;
        padding: 0 11px;
        color: #172033;
        font-size: 13px;
        outline: none;
    }
    .rex-nav-content-picker-controls input:focus,
    .rex-nav-content-picker-controls select:focus { border-color: #206bc4; box-shadow: 0 0 0 2px rgba(32,107,196,.1); }
    .rex-nav-content-picker-controls > button { width: 100%; }
    .rex-nav-content-picker-list { max-height: 320px; border-color: #eef1f6; }
    .rex-nav-content-choice { min-height: 40px; padding: 8px 9px; gap: 8px; }
    .rex-nav-content-choice span { font-size: 13px; }
    .rex-nav-content-choice small { color: #64748b; font-size: 10px; }
    .rex-nav-content-empty { padding: 12px; color: #64748b; font-size: 12px; }
    .rex-nav-custom-link-copy { margin: 0; color: #64748b; font-size: 12px; line-height: 1.5; }
    .rex-nav-list-panel { overflow: hidden; }
    .rex-nav-list { display: grid; gap: 8px; }
    .rex-nav-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 10px 12px;
        border-color: #e5eaf2;
        border-radius: 6px;
        box-shadow: none;
    }
    .rex-nav-item:hover { border-color: #cbd5e1; box-shadow: none; }
    .rex-nav-item--active { border-color: #93c5fd; background: #f8fbff; }
    .rex-nav-main { align-items: center; gap: 9px; }
    .rex-nav-order-indicator { width: 24px; height: 24px; border-radius: 6px; font-size: 11px; }
    .rex-nav-content h3 { overflow-wrap: anywhere; font-size: 14px; }
    .rex-nav-heading-row { align-items: center; }
    .rex-nav-badge { padding: 3px 7px; font-size: 10px; }
    .rex-nav-meta { overflow-wrap: anywhere; margin-top: 3px; font-size: 11px; }
    .rex-nav-nested { margin-top: 8px; padding-left: 10px; }
    .rex-nav-nested-item { flex-wrap: wrap; gap: 6px; padding: 4px 0; }
    .rex-nav-nested-details { min-width: 120px; flex: 1; font-size: 12px; }
    .rex-nav-actions { align-items: flex-end; gap: 6px; }
    .rex-nav-order-actions { flex-wrap: wrap; gap: 4px; }
    .rex-nav-order-button { width: 36px; height: 36px; border-radius: 6px; font-size: 10px; }
    .rex-icon-btn { width: 34px; height: 34px; }
    .rex-nav-edit-actions { gap: 4px; }
    .rex-nav-edit-actions .rex-nav-action-button { min-height: 30px; padding: 5px 8px; font-size: 12px; }
    .rex-nav-editor { position: static; padding: 16px; }
    .rex-nav-editor-head { padding-bottom: 12px; }
    .rex-nav-editor-head h2 { font-size: 15px; }
    .rex-nav-editor-head p { font-size: 12px; }
    .rex-nav-editor-kicker { letter-spacing: .1em; }
    .rex-nav-form { gap: 12px; padding-top: 12px; }
    .rex-nav-field { gap: 5px; }
    .rex-nav-field label { color: #475569; font-size: 12px; font-weight: 600; }
    .rex-nav-field input,
    .rex-nav-field select,
    .rex-nav-field textarea { min-height: 40px; border-color: #dbe2ed; border-radius: 6px; padding: 8px 11px; font-size: 13px; }
    .rex-nav-field input:focus,
    .rex-nav-field select:focus,
    .rex-nav-field textarea:focus { border-color: #206bc4; box-shadow: 0 0 0 2px rgba(32,107,196,.1); }
    .rex-nav-toggle-row { border-color: #e5eaf2; border-radius: 6px; background: #f8fafc; }
    .rex-nav-toggle input:focus-visible + .rex-nav-toggle-slider { outline: 2px solid #206bc4; outline-offset: 2px; }
    .rex-nav-actions-bar { padding-top: 0; }
    .rex-nav-inline-editor { grid-column: 1 / -1; min-width: 0; padding-top: 10px; border-top: 1px solid #eef1f6; }
    .rex-nav-nested-item > .rex-nav-inline-editor { flex-basis: 100%; }
    .rex-nav-inline-editor .rex-nav-editor-head { align-items: center; }
    .rex-nav-inline-editor .rex-nav-editor-icon { display: none; }
    .rex-modal-card { max-width: 440px; border: 1px solid #e5eaf2; border-radius: 8px; padding: 22px; }
    .rex-modal-title { color: #172033; font-size: 16px; }
    .rex-nav-inline-page .cms-error { border-radius: 6px; font-size: 12px; }
    @media (max-width: 960px) {
        .rex-nav-layout:not(.rex-footer-layout),
        .rex-footer-layout { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 640px) {
        .rex-nav-workspace-header { padding: 12px; }
        .rex-nav-workspace-actions { align-items: stretch; }
        .rex-nav-toolbar > * { flex: 1 1 auto; }
        .rex-nav-menu-select { min-width: min(100%, 180px); }
        .rex-nav-menu-select select { min-width: 0; width: 100%; }
        .rex-nav-create-menu { width: 100%; }
        .rex-nav-create-menu .cms-input { flex: 1; width: auto; }
        .rex-nav-add-panel,
        .rex-nav-list-panel { padding: 14px; }
        .rex-nav-item { grid-template-columns: minmax(0, 1fr); gap: 8px; }
        .rex-nav-actions { flex-direction: row; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .rex-nav-heading-row { align-items: flex-start; flex-direction: column; }
        .rex-nav-badges { justify-content: flex-start; }
        .rex-nav-two-up { grid-template-columns: minmax(0, 1fr); }
    }
</style>

<div class="rex-nav-inline-page"
    x-data="{ hasUnsavedChanges: false }"
    x-on:beforeunload.window="if (hasUnsavedChanges) { $event.preventDefault(); $event.returnValue = ''; }"
    x-on:livewire:navigate.document="if (hasUnsavedChanges && ! window.confirm('Leave this page and discard unsaved navigation changes?')) { $event.preventDefault(); }"
    x-on:navigation-draft-changed.window="hasUnsavedChanges = Boolean($event.detail.dirty)"
    x-on:navigation-saved.window="hasUnsavedChanges = false">
    <div class="rex-nav-inline-header">
        <div>
            <nav class="rex-nav-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('admin.appearance.themes') }}">Appearance</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Menus</span>
            </nav>
            <h1>Menus</h1>
            <div class="rex-nav-inline-subtext">Manage your website navigation and footer links.</div>
            <p x-cloak x-show="hasUnsavedChanges" class="mt-2 text-xs font-semibold text-amber-700" role="status">Unsaved changes</p>
        </div>
    </div>

    <!-- Workspace Tabs -->
    <section class="rex-nav-workspace">
        <div class="rex-nav-workspace-header">
            <div class="rex-nav-workspace-actions rex-nav-toolbar">
                <label class="rex-nav-menu-select">
                    <span class="rex-nav-workspace-label">Selected menu</span>
                    <select aria-label="Select menu" x-on:change="if (hasUnsavedChanges && ! window.confirm('Discard unsaved changes and switch menus?')) { $el.value = @js($menu); return; } if (hasUnsavedChanges) { $wire.discardDraftAndSelectMenu($el.value); } else { $wire.selectMenu($el.value); }">
                        @foreach ($menuOptions as $menuOption)
                            <option value="{{ $menuOption }}" @selected($menuOption === $menu)>{{ ucfirst(str_replace('-', ' ', $menuOption)) }} menu</option>
                        @endforeach
                    </select>
                </label>
                @if ($menu === 'footer')
                    <span class="rex-nav-toolbar-count">{{ $footerColumns->count() }} {{ str('column')->plural($footerColumns->count()) }} · {{ $footerLinkCount }} {{ str('link')->plural($footerLinkCount) }}</span>
                @elseif ($footerSubTab !== 'locations')
                    <span class="rex-nav-toolbar-count">{{ $items->count() }} {{ str('item')->plural($items->count()) }}</span>
                @endif
                <span class="rex-nav-save-status is-dirty" x-cloak x-show="hasUnsavedChanges" role="status" aria-live="polite">Unsaved changes</span>
                <span class="rex-nav-save-status" x-show="!hasUnsavedChanges" role="status" aria-live="polite">Saved</span>
                @can('manage-navigation')
                <button type="button" wire:click="$set('showCreateMenuModal', true)" class="rex-nav-action-button rex-nav-action-button--secondary">
                    <flux:icon name="plus" class="mr-1 inline size-4" />Create menu
                </button>
                @if ($menu !== 'footer' && ! in_array($menu, \App\Models\NavigationItem::MENUS, true))
                    <button type="button" wire:click="startRenameMenu" class="rex-nav-action-button rex-nav-action-button--secondary">Rename menu</button>
                    <button type="button" wire:click="$set('showDeleteMenuModal', true)" class="rex-nav-action-button rex-nav-action-button--danger">Delete menu</button>
                @endif
                @endcan
                @if ($menu !== 'footer' && $footerSubTab !== 'locations')
                    <button type="button" wire:click="openLocations" class="rex-nav-action-button rex-nav-action-button--secondary">
                        <flux:icon name="arrows-right-left" class="mr-1 inline size-4" />Manage locations
                    </button>
                @endif
                <button type="button" wire:click="saveMenu" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled" @disabled(! $menuDraftDirty)>
                    <span wire:loading.remove wire:target="saveMenu">Save menu</span>
                    <span wire:loading wire:target="saveMenu">Saving menu...</span>
                </button>
                @if ($menuDraftDirty)
                    <button type="button" wire:click="discardDraft" wire:confirm="Discard all unsaved changes to this menu?" class="rex-nav-action-button rex-nav-action-button--secondary">Discard changes</button>
                @endif
            </div>
        </div>

        <!-- Footer Specific Sub Navigation Controls -->
        @if ($menu === 'footer')
            <div class="rex-footer-subnav">
                <div class="rex-footer-subnav-group">
                    <button type="button"
                        wire:click="setFooterSubTab('structure')"
                        class="rex-footer-subnav-btn {{ $footerSubTab === 'structure' ? 'rex-footer-subnav-btn--active' : '' }}">
                        <flux:icon name="bars-3-bottom-left" class="size-4" />
                        <span>Footer Structure</span>
                    </button>
                    <button type="button"
                        wire:click="setFooterSubTab('settings')"
                        class="rex-footer-subnav-btn {{ $footerSubTab === 'settings' ? 'rex-footer-subnav-btn--active' : '' }}">
                        <flux:icon name="cog-6-tooth" class="size-4" />
                        <span>Footer Settings</span>
                    </button>
                    <button type="button"
                        wire:click="setFooterSubTab('locations')"
                        class="rex-footer-subnav-btn {{ $footerSubTab === 'locations' ? 'rex-footer-subnav-btn--active' : '' }}">
                        <flux:icon name="arrows-right-left" class="size-4" />
                        <span>Theme Locations</span>
                    </button>
                    <a href="{{ route('admin.appearance.nova-footer') }}" wire:navigate class="rex-footer-subnav-btn">
                        <flux:icon name="paint-brush" class="size-4" />
                        <span>Rexxsol Nova Design</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if ($footerSubTab === 'structure')
                        <button type="button"
                            wire:click="openAddColumn"
                            class="rex-nav-action-button rex-nav-action-button--primary">
                            <flux:icon name="plus" class="mr-1 inline size-4" />
                            <span>Add Footer Column</span>
                        </button>
                    @endif
                    <button type="button"
                        wire:click="$toggle('showPreview')"
                        class="rex-nav-action-button rex-nav-action-button--secondary">
                        <flux:icon name="eye" class="mr-1 inline size-4" />
                        <span>{{ $showPreview ? 'Hide Preview' : 'Footer Preview' }}</span>
                    </button>
                </div>
            </div>
        @endif
    </section>

    <!-- Optional Footer Live Preview -->
    @if ($menu === 'footer' && $showPreview && $previewData)
        <section class="rex-preview-card">
            <div class="rex-preview-header">
                <div>
                    <h3 class="text-sm font-bold text-[#1e293b]">Live Footer Preview ({{ $activeTheme['name'] }})</h3>
                    <p class="text-xs text-[#64748b]">A simplified representation of how your footer appears to visitors.</p>
                </div>
                <button type="button" wire:click="$toggle('showPreview')" class="text-xs text-[#64748b] hover:text-[#0f172a]">Close</button>
            </div>
            <div class="rex-preview-mockup">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="font-extrabold tracking-wider text-[#0f172a]">{{ $previewData['brand']['name'] }}</div>
                        <p class="mt-2 text-xs text-[#64748b]">{{ Str::limit($previewData['brand']['description'], 120) }}</p>
                        @if (!empty($previewData['socials']))
                            <div class="mt-3 flex gap-2">
                                @foreach ($previewData['socials'] as $soc)
                                    <span class="inline-flex size-6 items-center justify-center rounded-full bg-[#f1f5f9] text-[11px] text-[#475569]"><flux:icon name="share" class="size-3" /></span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @foreach ($previewData['columns'] as $prevCol)
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#0f172a]">{{ $prevCol['heading'] }}</h4>
                            <ul class="mt-2 space-y-1.5 text-xs text-[#475569]">
                                @foreach ($prevCol['links'] as $prevLink)
                                    <li>{{ $prevLink['label'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 flex flex-col items-center justify-between border-t border-[#f1f5f9] pt-4 text-xs text-[#94a3b8] sm:flex-row">
                    <p>{{ $previewData['copyright'] }}</p>
                    @if (!empty($previewData['legal']))
                        <div class="flex gap-3">
                            @foreach ($previewData['legal'] as $prevLegal)
                                <span>{{ $prevLegal['label'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <!-- ========================================================
         FOOTER MENU: SUB-TAB 1 — STRUCTURE
    ========================================================= -->
    @if ($menu === 'footer' && $footerSubTab === 'structure')
        <div class="rex-nav-layout rex-footer-layout">
            <!-- Left: Footer Columns & Links -->
            <section class="rex-nav-list-panel" aria-labelledby="footer-structure-heading">
                <div class="rex-nav-panel-head">
                    <div>
                        <h2 id="footer-structure-heading" class="rex-nav-panel-title">Footer menu items & column structure</h2>
                        <p class="rex-nav-panel-copy">Organize footer columns, link destinations, and hierarchy.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rex-nav-count">{{ $footerColumns->count() }} {{ str('column')->plural($footerColumns->count()) }}</span>
                        <span class="rex-nav-count">{{ $footerLinkCount }} {{ str('link')->plural($footerLinkCount) }}</span>
                    </div>
                </div>
                @error('footerLinks')<p class="cms-error" role="alert">{{ $message }}</p>@enderror

                @if ($footerColumns->isEmpty())
                    <div class="rex-empty-box">
                        <span class="rex-empty-icon"><flux:icon name="rectangle-group" class="size-6" /></span>
                        <h3 class="text-base font-bold text-[#0f172a]">Build your footer navigation</h3>
                        <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-[#64748b]">
                            Create your first footer column and add links such as About, Services, Contact or Privacy Policy.
                        </p>
                        <button type="button" wire:click="openAddColumn" class="rex-nav-action-button rex-nav-action-button--primary mt-4">
                            <flux:icon name="plus" class="mr-1 size-4" />
                            <span>Add footer column</span>
                        </button>
                    </div>
                @else
                    <div class="space-y-3" id="footer-columns-container">
                        @foreach ($footerColumns as $col)
                            @php
                                $isCollapsed = in_array($col->id, $collapsedColumns, true);
                            @endphp
                            <div wire:key="col-{{ $col->id }}" class="rex-footer-col {{ ! $col->enabled ? 'rex-footer-col--disabled' : '' }}">
                                <!-- Column Header -->
                                <div class="rex-footer-col-header">
                                    <div class="rex-footer-col-title-area">
                                        <span class="rex-drag-handle" title="Drag to reorder"><flux:icon name="bars-2" class="size-4" /></span>
                                        <button type="button" wire:click="toggleCollapseColumn({{ $col->id }})" class="text-[#64748b] hover:text-[#0f172a]">
                                            <flux:icon name="{{ $isCollapsed ? 'chevron-right' : 'chevron-down' }}" class="size-4" />
                                        </button>
                                        <h3 class="rex-footer-col-title">{{ $col->label }}</h3>
                                        <span class="rex-badge">{{ $col->children->count() }} {{ str('link')->plural($col->children->count()) }}</span>
                                        <span class="rex-badge {{ $col->enabled ? 'rex-badge--success' : 'rex-badge--muted' }}">
                                            {{ $col->enabled ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>

                                    <div class="rex-footer-col-actions">
                                        <button type="button" wire:click="moveColumn({{ $col->id }}, 'up')" class="rex-icon-btn" title="Move column up"><flux:icon name="chevron-up" class="size-3.5" /></button>
                                        <button type="button" wire:click="moveColumn({{ $col->id }}, 'down')" class="rex-icon-btn" title="Move column down"><flux:icon name="chevron-down" class="size-3.5" /></button>
                                        <button type="button" wire:click="prepareAddLink({{ $col->id }})" class="rex-icon-btn" title="Add link to this column"><flux:icon name="plus" class="size-3.5" /></button>
                                        <button type="button" wire:click="startEditColumn({{ $col->id }})" class="rex-icon-btn" title="Rename column"><flux:icon name="pencil-square" class="size-3.5" /></button>
                                        <button type="button" wire:click="toggleColumn({{ $col->id }})" class="rex-icon-btn" title="{{ $col->enabled ? 'Disable column' : 'Enable column' }}"><flux:icon name="{{ $col->enabled ? 'eye' : 'eye-slash' }}" class="size-3.5" /></button>
                                        <button type="button" wire:click="confirmDeleteColumn({{ $col->id }})" class="rex-icon-btn rex-icon-btn--danger" title="Delete column"><flux:icon name="trash" class="size-3.5" /></button>
                                    </div>
                                </div>

                                <!-- Column Body (Links) -->
                                @if (! $isCollapsed)
                                    <div class="rex-footer-col-body">
                                        @forelse ($col->children as $link)
                                            @php
                                                $isBroken = $link->isTargetUnavailable();
                                                $resolvedUrl = app(App\Support\FooterService::class)->url($link) ?? ($link->url ?: '#');
                                            @endphp
                                            <div wire:key="link-{{ $link->id }}" class="rex-link-item {{ $editingId === $link->id ? 'rex-link-item--active' : '' }} {{ ! $link->enabled ? 'rex-link-item--disabled' : '' }}">
                                                <div class="rex-link-item-main">
                                                    <span class="rex-drag-handle" title="Drag to reorder"><flux:icon name="bars-2" class="size-3.5" /></span>
                                                    <div class="rex-link-item-info">
                                                        <div class="rex-link-item-title-row">
                                                            <span class="rex-link-item-title">{{ $link->label }}</span>
                                                            <span class="rex-badge">{{ $link->type === 'custom' ? 'Custom URL' : ($link->type === 'system' ? 'System Link' : ucfirst($link->type)) }}</span>
                                                            <span class="rex-badge {{ $link->enabled ? 'rex-badge--success' : 'rex-badge--muted' }}">{{ $link->enabled ? 'Enabled' : 'Disabled' }}</span>
                                                            @if ($isBroken)
                                                                <span class="rex-badge rex-badge--warning" title="The linked page, service, or article is deleted or unpublished.">
                                                                    ⚠️ Content unavailable
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="rex-link-item-meta">{{ $resolvedUrl }}</p>
                                                    </div>
                                                </div>

                                                <div class="rex-link-item-actions">
                                                    <button type="button" wire:click="moveLink({{ $link->id }}, 'up')" class="rex-icon-btn" title="Move up"><flux:icon name="chevron-up" class="size-3" /></button>
                                                    <button type="button" wire:click="moveLink({{ $link->id }}, 'down')" class="rex-icon-btn" title="Move down"><flux:icon name="chevron-down" class="size-3" /></button>
                                                    <button type="button" wire:click="editLink({{ $link->id }})" class="rex-icon-btn" title="Edit link"><flux:icon name="pencil" class="size-3" /></button>
                                                    <button type="button" wire:click="toggleLink({{ $link->id }})" class="rex-icon-btn" title="{{ $link->enabled ? 'Disable' : 'Enable' }}"><flux:icon name="{{ $link->enabled ? 'eye' : 'eye-slash' }}" class="size-3" /></button>
                                                    <button type="button" wire:click="removeLink({{ $link->id }})" wire:confirm="Remove this link from the footer?" class="rex-icon-btn rex-icon-btn--danger" title="Remove link"><flux:icon name="trash" class="size-3" /></button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-3 text-center text-xs text-[#94a3b8]">
                                                No links in this column yet.
                                                <button type="button" wire:click="prepareAddLink({{ $col->id }})" class="ml-1 font-semibold text-[#2563eb] hover:underline">Add a link</button>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Right: Link Editor -->
            <aside class="rex-nav-editor">
                <div class="rex-nav-editor-head">
                    <div>
                        <p class="rex-nav-editor-kicker">FOOTER LINK</p>
                        <h2>{{ $editingId ? 'Edit footer link' : 'Add footer link' }}</h2>
                        <p>Configure link destination, column placement and visibility.</p>
                    </div>
                    <span class="rex-nav-editor-icon"><flux:icon name="link" class="size-5" /></span>
                </div>

                <form wire:submit="saveLink" class="rex-nav-form" x-on:input="hasUnsavedChanges = true" x-on:change="hasUnsavedChanges = true">
                    <!-- Footer Column Selection -->
                    <div class="rex-nav-field">
                        <label for="link-column">Footer Column</label>
                        <select id="link-column" wire:model="linkColumnId">
                            <option value="">Select column...</option>
                            @foreach ($footerColumns as $colOption)
                                <option value="{{ $colOption->id }}">{{ $colOption->label }}</option>
                            @endforeach
                        </select>
                        @error('linkColumnId')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>

                    <!-- Label -->
                    <div class="rex-nav-field">
                        <label for="link-label">Label</label>
                        <input id="link-label" wire:model="label" placeholder="e.g. Services, About Us, Privacy Policy" />
                        @error('label')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>

                    <!-- Type -->
                    <div class="rex-nav-field">
                        <label for="link-type">Type</label>
                        <select id="link-type" wire:model.live="type">
                            <option value="page">Internal Page</option>
                            <option value="service">Service</option>
                            <option value="project">Portfolio</option>
                            <option value="blog">Blog</option>
                            <option value="contact">Contact</option>
                            <option value="system">System Link</option>
                            <option value="custom">Custom URL</option>
                        </select>
                    </div>

                    <!-- Dynamic Destination Based on Type -->
                    @if ($type === 'page')
                        <div class="rex-nav-field">
                            <label for="link-page-search">Published Page</label>
                            <input type="text"
                                id="link-page-search"
                                wire:model.live.debounce.250ms="pageSearch"
                                placeholder="Search published pages..."
                                class="mb-1.5" />
                            <select id="link-page-target" wire:model.live="targetId" size="5" class="h-28">
                                <option value="">-- Choose published page --</option>
                                @forelse ($publishedPages as $pg)
                                    <option value="{{ $pg->id }}">{{ $pg->title }} (/{{ $pg->slug }})</option>
                                @empty
                                    <option disabled>No matching published pages</option>
                                @endforelse
                            </select>
                            @error('targetId')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @elseif ($type === 'service')
                        <div class="rex-nav-field">
                            <label for="link-service-target">Published Service</label>
                            <select id="link-service-target" wire:model.live="targetId">
                                <option value="">Choose service...</option>
                                @foreach ($publishedServices as $svc)
                                    <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                                @endforeach
                            </select>
                            @error('targetId')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @elseif ($type === 'project')
                        <div class="rex-nav-field">
                            <label for="link-project-target">Published Project</label>
                            <select id="link-project-target" wire:model.live="targetId">
                                <option value="">Choose project...</option>
                                @foreach ($options['project'] ?? [] as $prj)
                                    <option value="{{ $prj->id }}">{{ $prj->name }}</option>
                                @endforeach
                            </select>
                            @error('targetId')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @elseif ($type === 'blog')
                        <div class="rex-nav-field">
                            <label for="link-blog-target">Published Article</label>
                            <select id="link-blog-target" wire:model.live="targetId">
                                <option value="">Choose article...</option>
                                @foreach ($options['blog'] ?? [] as $blg)
                                    <option value="{{ $blg->id }}">{{ $blg->title }}</option>
                                @endforeach
                            </select>
                            @error('targetId')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @elseif ($type === 'contact')
                        <div class="rounded-lg border border-[#e2e8f0] bg-[#f8fafc] p-3 text-xs text-[#64748b]">
                            Points directly to the public Contact page (<span class="font-mono text-[#0f172a]">/contact</span>).
                        </div>
                    @elseif ($type === 'system')
                        <div class="rex-nav-field">
                            <label for="link-system-url">System Route</label>
                            <select id="link-system-url" wire:model.live="url">
                                <option value="">Choose system route...</option>
                                @foreach ($this->systemOptions() as $k => $sysLabel)
                                    <option value="{{ $k }}">{{ $sysLabel }}</option>
                                @endforeach
                            </select>
                            @error('url')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @elseif ($type === 'custom')
                        <div class="rex-nav-field">
                            <label for="link-custom-url">URL or Relative Path</label>
                            <input id="link-custom-url" wire:model="url" placeholder="https://example.com or /terms" />
                            @error('url')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <!-- Two-Up: Target & Visibility -->
                    <div class="rex-nav-two-up">
                        <div class="rex-nav-field">
                            <label for="link-target-window">Open In</label>
                            <select id="link-target-window" wire:model="target">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>

                        <div class="rex-nav-toggle-row">
                            <div>
                                <label for="link-enabled" class="text-xs font-semibold text-[#1e293b]">Enabled</label>
                                <p class="rex-nav-toggle-help">Visible in footer</p>
                            </div>
                            <label class="rex-nav-toggle" for="link-enabled">
                                <input id="link-enabled" type="checkbox" wire:model="enabled" />
                                <span class="rex-nav-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="rex-nav-actions-bar">
                        <button type="button" wire:click="resetLinkForm" class="rex-nav-action-button rex-nav-action-button--secondary">Clear</button>
                        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveLink">{{ $editingId ? 'Update link' : 'Add link' }}</span>
                            <span wire:loading wire:target="saveLink">Saving...</span>
                        </button>
                    </div>
                </form>
            </aside>
        </div>
    @endif

    <!-- ========================================================
         FOOTER MENU: SUB-TAB 2 — SETTINGS
    ========================================================= -->
    @if ($menu === 'footer' && $footerSubTab === 'settings')
        <div class="rex-settings-grid" x-on:input="hasUnsavedChanges = true" x-on:change="hasUnsavedChanges = true">
            <!-- 1. Brand -->
            <div class="rex-settings-card">
                <div class="rex-settings-card-head">
                    <flux:icon name="sparkles" class="size-5 text-[#2563eb]" />
                    <div>
                        <h3 class="rex-settings-card-title">Brand & Identity</h3>
                        <p class="rex-settings-card-desc">Brand assets and summary displayed in the theme footer.</p>
                    </div>
                </div>
                <div class="rex-settings-card-body">
                    <div class="rex-nav-field">
                        <label for="f-site-name">Company / Site Name</label>
                        <input id="f-site-name" wire:model="siteName" placeholder="e.g. Rexxsol" />
                        @error('siteName')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-footer-logo">Footer Logo URL</label>
                        <input id="f-footer-logo" wire:model="footerLogoUrl" placeholder="https://... or /images/logo.png" />
                        @error('footerLogoUrl')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field col-span-full">
                        <label for="f-footer-desc">Company Short Description</label>
                        <textarea id="f-footer-desc" wire:model="footerDescription" rows="3" placeholder="Brief description of your business shown in footer..."></textarea>
                        @error('footerDescription')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- 2. Contact -->
            <div class="rex-settings-card">
                <div class="rex-settings-card-head">
                    <flux:icon name="envelope" class="size-5 text-[#2563eb]" />
                    <div>
                        <h3 class="rex-settings-card-title">Contact Information</h3>
                        <p class="rex-settings-card-desc">Reuses global company settings so your contact details remain consistent.</p>
                    </div>
                </div>
                <div class="rex-settings-card-body">
                    <div class="rex-nav-field">
                        <label for="f-contact-email">Business Email</label>
                        <input id="f-contact-email" type="email" wire:model="contactEmail" placeholder="hello@example.com" />
                        @error('contactEmail')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-contact-phone">Phone Number</label>
                        <input id="f-contact-phone" wire:model="contactPhone" placeholder="+1 (555) 000-0000" />
                        @error('contactPhone')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-contact-whatsapp">WhatsApp Number</label>
                        <input id="f-contact-whatsapp" wire:model="contactWhatsapp" placeholder="+1 (555) 000-0000" />
                        @error('contactWhatsapp')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field col-span-full">
                        <label for="f-contact-addr">Physical Address</label>
                        <textarea id="f-contact-addr" wire:model="contactAddress" rows="2" placeholder="Street, City, Country"></textarea>
                        @error('contactAddress')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- 3. Social Media Links -->
            <div class="rex-settings-card">
                <div class="rex-settings-card-head">
                    <flux:icon name="share" class="size-5 text-[#2563eb]" />
                    <div>
                        <h3 class="rex-settings-card-title">Social Links</h3>
                        <p class="rex-settings-card-desc">Only social networks with valid URLs will be rendered on the website.</p>
                    </div>
                </div>
                <div class="rex-settings-card-body">
                    <div class="rex-nav-field">
                        <label for="f-soc-linkedin">LinkedIn</label>
                        <input id="f-soc-linkedin" wire:model="socialLinkedin" placeholder="https://linkedin.com/company/..." />
                        @error('socialLinkedin')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-soc-facebook">Facebook</label>
                        <input id="f-soc-facebook" wire:model="socialFacebook" placeholder="https://facebook.com/..." />
                        @error('socialFacebook')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-soc-instagram">Instagram</label>
                        <input id="f-soc-instagram" wire:model="socialInstagram" placeholder="https://instagram.com/..." />
                        @error('socialInstagram')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-soc-youtube">YouTube</label>
                        <input id="f-soc-youtube" wire:model="socialYoutube" placeholder="https://youtube.com/..." />
                        @error('socialYoutube')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="rex-nav-field">
                        <label for="f-soc-twitter">X / Twitter</label>
                        <input id="f-soc-twitter" wire:model="socialTwitter" placeholder="https://x.com/..." />
                        @error('socialTwitter')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- 4. Bottom Bar -->
            <div class="rex-settings-card">
                <div class="rex-settings-card-head">
                    <flux:icon name="document-text" class="size-5 text-[#2563eb]" />
                    <div>
                        <h3 class="rex-settings-card-title">Bottom Bar</h3>
                        <p class="rex-settings-card-desc">Theme copyright text is managed in Theme Options.</p>
                    </div>
                </div>
                <div class="rex-settings-card-body">
                    <a href="{{ route('admin.appearance.theme-options') }}" wire:navigate class="text-sm font-semibold text-[#1d4ed8] hover:underline">Edit copyright in Theme Options</a>
                </div>
            </div>

            <!-- 5. Theme Footer CTA (Only if theme supports it) -->
            @if ($footerCapabilities['supports_cta'] ?? false)
                <div class="rex-settings-card">
                    <div class="rex-settings-card-head">
                        <flux:icon name="megaphone" class="size-5 text-[#2563eb]" />
                        <div>
                            <h3 class="rex-settings-card-title">Theme Footer Call-to-Action</h3>
                            <p class="rex-settings-card-desc">Declared by the active theme ({{ $activeTheme['name'] }}).</p>
                        </div>
                    </div>
                    <div class="rex-settings-card-body">
                        <div class="rex-nav-toggle-row col-span-full">
                            <div>
                                <label for="f-cta-enabled" class="text-xs font-semibold text-[#1e293b]">Enable Footer CTA</label>
                                <p class="rex-nav-toggle-help">Display banner CTA above footer columns</p>
                            </div>
                            <label class="rex-nav-toggle" for="f-cta-enabled">
                                <input id="f-cta-enabled" type="checkbox" wire:model="ctaEnabled" />
                                <span class="rex-nav-toggle-slider"></span>
                            </label>
                        </div>
                        <div class="rex-nav-field">
                            <label for="f-cta-eyebrow">Eyebrow</label>
                            <input id="f-cta-eyebrow" wire:model="ctaEyebrow" placeholder="e.g. Ready to begin?" />
                        </div>
                        <div class="rex-nav-field">
                            <label for="f-cta-heading">Heading</label>
                            <input id="f-cta-heading" wire:model="ctaHeading" placeholder="e.g. Let’s build something great together." />
                        </div>
                        <div class="rex-nav-field col-span-full">
                            <label for="f-cta-desc">Description</label>
                            <textarea id="f-cta-desc" wire:model="ctaDescription" rows="2"></textarea>
                        </div>
                        <div class="rex-nav-field">
                            <label for="f-cta-btn-lbl">Button Label</label>
                            <input id="f-cta-btn-lbl" wire:model="ctaButtonLabel" placeholder="Start a project" />
                        </div>
                        <div class="rex-nav-field">
                            <label for="f-cta-btn-url">Button URL</label>
                            <input id="f-cta-btn-url" wire:model="ctaButtonUrl" placeholder="/contact" />
                        </div>
                    </div>
                </div>
            @endif

            <!-- Save Bar -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" wire:click="loadFooterSettings" class="rex-nav-action-button rex-nav-action-button--secondary">Reset</button>
                <button type="button" wire:click="saveFooterSettings" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveFooterSettings">Save Footer Settings</span>
                    <span wire:loading wire:target="saveFooterSettings">Saving...</span>
                </button>
            </div>
        </div>
    @endif

    <!-- ========================================================
         FOOTER MENU: SUB-TAB 3 — LOCATIONS
    ========================================================= -->
    @if ($menu === 'footer' && $footerSubTab === 'locations')
        <div class="rex-locations-card">
            <div class="rex-nav-panel-head">
                <div>
                    <h2 class="rex-nav-panel-title">Manage Theme Footer Locations</h2>
                    <p class="rex-nav-panel-copy">
                        Map your footer columns and menus to registered locations in the active theme (<span class="font-semibold text-[#0f172a]">{{ $activeTheme['name'] }}</span>).
                    </p>
                </div>
            </div>

            <table class="rex-locations-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Theme Footer Location</th>
                        <th style="width: 60%;">Assigned Footer Column or Menu</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $locs = $footerCapabilities['locations'] ?? [];
                    @endphp
                    @forelse ($locs as $locKey => $locLabel)
                        <tr>
                            <td>
                                <span class="font-bold text-[#1e293b]">{{ $locLabel }}</span>
                                <span class="block text-xs font-mono text-[#64748b]">{{ $locKey }}</span>
                            </td>
                            <td>
                                <select wire:model="locationAssignments.{{ $locKey }}" x-on:change="hasUnsavedChanges = true" class="w-full rounded-lg border border-[#dce4ef] p-2 text-xs">
                                    <option value="">Auto (Default Column)</option>
                                    <optgroup label="Footer Columns">
                                        @foreach ($footerColumns as $fc)
                                            <option value="column:{{ $fc->id }}">Column: {{ $fc->label }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Registered Menus">
                                        @foreach ($menuOptions as $mo)
                                            <option value="{{ $mo }}">{{ ucfirst(str_replace('-', ' ', $mo)) }} Menu</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-6 text-center text-xs text-[#94a3b8]">
                                The active theme declares no dedicated footer locations.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="saveLocations" class="rex-nav-action-button rex-nav-action-button--primary">
                    Save Location Assignments
                </button>
            </div>
        </div>
    @endif

    <!-- ========================================================
         PRIMARY MENU (BACKWARD COMPATIBILITY)
    ========================================================= -->
    @if ($menu !== 'footer')
        <div class="rex-nav-layout">
            <section class="rex-nav-add-panel" aria-labelledby="navigation-add-heading">
                <div class="rex-nav-panel-head">
                    <div>
                        <h2 id="navigation-add-heading" class="rex-nav-panel-title">Add menu items</h2>
                        <p class="rex-nav-panel-copy">Add published content or create a custom link.</p>
                    </div>
                </div>

                <details class="rex-nav-add-group" open>
                    <summary>Published content</summary>
                    <div class="rex-nav-add-group-body">
                        <section class="rex-nav-content-picker" aria-labelledby="navigation-content-picker-title">
                            <div class="rex-nav-content-picker-head">
                                <div>
                                    <h3 id="navigation-content-picker-title">Choose content</h3>
                                    <p>Select one or more published records.</p>
                                </div>
                                <span class="rex-nav-count">{{ count($selectedContentIds) }} selected</span>
                            </div>
                            <div class="rex-nav-content-picker-controls">
                                <label>
                                    <span>Content type</span>
                                    <select wire:model.live="selectedContentType" aria-label="Content type">
                                        <option value="page">Pages</option>
                                        <option value="service">Services</option>
                                        <option value="project">Projects</option>
                                        <option value="blog">Blog posts</option>
                                    </select>
                                </label>
                                <label class="rex-nav-content-search">
                                    <span>Search</span>
                                    <input type="search" wire:model.live.debounce.250ms="contentSearch" placeholder="Search published content..." aria-label="Search published content">
                                </label>
                                @php($pickerItems = $options[$selectedContentType] ?? collect())
                                <div class="rex-nav-content-picker-list" wire:loading.class="is-loading" wire:target="contentSearch,selectedContentType">
                                    @forelse ($pickerItems as $pickerItem)
                                        <label class="rex-nav-content-choice" wire:key="content-option-{{ $selectedContentType }}-{{ $pickerItem->id }}">
                                            <input type="checkbox" wire:model="selectedContentIds" value="{{ $pickerItem->id }}" x-on:change="hasUnsavedChanges = true">
                                            <span>{{ in_array($selectedContentType, ['service', 'project'], true) ? $pickerItem->name : $pickerItem->title }}</span>
                                            <small>{{ ucfirst($selectedContentType) }}</small>
                                        </label>
                                    @empty
                                        <p class="rex-nav-content-empty">No published {{ str($selectedContentType)->plural() }} match your search.</p>
                                    @endforelse
                                </div>
                                @error('selectedContentIds')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
                                <button type="button" wire:click="addSelectedContent" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled" @disabled(count($selectedContentIds) === 0)>
                                    <flux:icon name="plus" class="mr-1 inline size-4" />Add to menu
                                </button>
                            </div>
                        </section>
                    </div>
                </details>

                <details class="rex-nav-add-group">
                    <summary>Custom link</summary>
                    <div class="rex-nav-add-group-body">
                        <p class="rex-nav-custom-link-copy">Add a link to any safe URL, choose its parent, and decide whether it opens in a new tab.</p>
                        <button type="button" wire:click="startCustomLink" class="rex-nav-action-button rex-nav-action-button--secondary">
                            <flux:icon name="plus" class="mr-1 inline size-4" />Add custom link
                        </button>
                    </div>
                </details>
            </section>

            <div class="rex-nav-structure-column">
                <section class="rex-nav-list-panel" aria-labelledby="navigation-items-heading">
                    <div class="rex-nav-panel-head">
                        <div>
                            <h2 id="navigation-items-heading" class="rex-nav-panel-title">Menu structure</h2>
                            <p class="rex-nav-panel-copy">Drag to reorder, indent to nest, or edit an item inline.</p>
                        </div>
                        <span class="rex-nav-count">{{ $items->count() }} {{ str('item')->plural($items->count()) }}</span>
                    </div>
                    @error('menuItems')<p class="cms-error" role="alert">{{ $message }}</p>@enderror

                <div class="rex-nav-list" data-navigation-sortable
                    x-data="{
                        draggedId: null,
                        reorderBefore(targetId) {
                            if (!this.draggedId || this.draggedId === targetId) return;
                            const ids = Array.from(this.$el.querySelectorAll('[data-menu-item-id]')).map((item) => item.dataset.menuItemId);
                            const draggedIndex = ids.indexOf(this.draggedId);
                            if (draggedIndex < 0 || !ids.includes(targetId)) return;
                            ids.splice(draggedIndex, 1);
                            ids.splice(ids.indexOf(targetId), 0, this.draggedId);
                            this.$wire.reorderItems(ids);
                            this.draggedId = null;
                        }
                    }">
                    @forelse ($items as $item)
                        @php($isEditingItem = $editingId !== null && (int) $editingId === (int) $item->id)
                        <article wire:key="nav-{{ $item->id }}" class="rex-nav-item {{ $isEditingItem ? 'rex-nav-item--active' : '' }}" data-menu-item-id="{{ $item->id }}" draggable="true"
                            x-on:dragstart="draggedId = $event.currentTarget.dataset.menuItemId; $event.dataTransfer.effectAllowed = 'move'"
                            x-on:dragover.prevent
                            x-on:drop.prevent.stop="reorderBefore($event.currentTarget.dataset.menuItemId)"
                            x-on:dragend="draggedId = null">
                            <div class="rex-nav-main">
                                <span class="rex-drag-handle" aria-hidden="true" title="Drag to reorder"><flux:icon name="bars-2" class="size-4" /></span>
                                <div class="rex-nav-order-indicator" aria-hidden="true">
                                    <span>{{ $loop->iteration }}</span>
                                </div>

                                <div class="rex-nav-content">
                                    <div class="rex-nav-heading-row">
                                        <h3>{{ $item->label }}</h3>
                                        <div class="rex-nav-badges">
                                            <span class="rex-nav-badge">{{ $item->type === 'custom' ? 'Custom URL' : ($item->type === 'system' ? 'System Link' : ucfirst($item->type)) }}</span>
                                            <span class="rex-nav-badge {{ $item->enabled ? 'rex-nav-badge--success' : 'rex-nav-badge--muted' }}">{{ $item->enabled ? 'Enabled' : 'Disabled' }}</span>
                                        </div>
                                    </div>

                                    <p class="rex-nav-meta">
                                        {{ $item->type === 'custom' ? ($item->url ?: 'Custom URL') : ($item->type === 'system' ? ($item->url ?: 'System link') : 'Published content reference') }}
                                    </p>

                                    @if ($item->children->isNotEmpty())
                                        <div class="rex-nav-nested">
                                            @foreach ($item->children as $child)
                                                @php($isEditingChild = $editingId !== null && (int) $editingId === (int) $child->id)
                                                <div wire:key="nav-child-{{ $child->id }}" class="rex-nav-nested-item">
                                                    <span class="rex-nav-nested-line"></span>
                                                    <div class="rex-nav-nested-details">
                                                        <span>{{ $child->label }}</span>
                                                        <span class="rex-nav-badge {{ $child->enabled ? 'rex-nav-badge--success' : 'rex-nav-badge--muted' }}">{{ $child->enabled ? 'Enabled' : 'Disabled' }}</span>
                                                    </div>
                                                    <div class="rex-nav-order-actions">
                                                        <button type="button" wire:click="move({{ $child->id }}, 'up')" aria-label="Move {{ $child->label }} up" title="Move up" class="rex-nav-order-button"><flux:icon name="chevron-up" class="size-4" /></button>
                                                        <button type="button" wire:click="move({{ $child->id }}, 'down')" aria-label="Move {{ $child->label }} down" title="Move down" class="rex-nav-order-button"><flux:icon name="chevron-down" class="size-4" /></button>
                                                        <button type="button" wire:click="outdent({{ $child->id }})" aria-label="Move {{ $child->label }} out one level" title="Outdent" class="rex-nav-order-button">Outdent</button>
                                                    </div>
                                                    <div class="rex-nav-edit-actions">
                                                        <button type="button" wire:click="{{ $isEditingChild ? 'resetForm' : 'edit(' . $child->id . ')' }}" aria-label="{{ $isEditingChild ? 'Collapse editor for ' . $child->label : 'Expand editor for ' . $child->label }}" aria-expanded="{{ $isEditingChild ? 'true' : 'false' }}" class="rex-nav-action-button rex-nav-action-button--secondary">
                                                            <flux:icon name="{{ $isEditingChild ? 'chevron-up' : 'chevron-down' }}" class="mr-1 inline size-4" />{{ $isEditingChild ? 'Close' : 'Edit' }}
                                                        </button>
                                                        <button type="button" wire:click="remove({{ $child->id }})" wire:confirm="Remove this navigation item?" class="rex-nav-action-button rex-nav-action-button--danger">Remove</button>
                                                    </div>
                                                    @if ($isEditingChild)
                                                        <div class="rex-nav-inline-editor">
                                                            @include('livewire.admin.partials.navigation-item-editor', ['prefix' => 'navigation-child-' . $child->id])
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="rex-nav-actions">
                                <div class="rex-nav-order-actions">
                                    <button type="button" wire:click="move({{ $item->id }}, 'up')" aria-label="Move {{ $item->label }} up" title="Move up" class="rex-nav-order-button"><flux:icon name="chevron-up" class="size-4" /></button>
                                    <button type="button" wire:click="move({{ $item->id }}, 'down')" aria-label="Move {{ $item->label }} down" title="Move down" class="rex-nav-order-button"><flux:icon name="chevron-down" class="size-4" /></button>
                                    <button type="button" wire:click="indent({{ $item->id }})" aria-label="Indent {{ $item->label }} under the previous item" title="Indent under previous item" class="rex-nav-order-button">Indent</button>
                                </div>
                                <div class="rex-nav-edit-actions">
                                    <button type="button" wire:click="{{ $isEditingItem ? 'resetForm' : 'edit(' . $item->id . ')' }}" aria-label="{{ $isEditingItem ? 'Collapse editor for ' . $item->label : 'Expand editor for ' . $item->label }}" aria-expanded="{{ $isEditingItem ? 'true' : 'false' }}" class="rex-nav-action-button rex-nav-action-button--secondary">
                                        <flux:icon name="{{ $isEditingItem ? 'chevron-up' : 'chevron-down' }}" class="mr-1 inline size-4" />{{ $isEditingItem ? 'Close' : 'Edit' }}
                                    </button>
                                    <button type="button" wire:click="remove({{ $item->id }})" wire:confirm="Remove this navigation item?" class="rex-nav-action-button rex-nav-action-button--danger">Remove</button>
                                </div>
                            </div>
                            @if ($isEditingItem)
                                <div class="rex-nav-inline-editor">
                                    @include('livewire.admin.partials.navigation-item-editor', ['prefix' => 'navigation-item-' . $item->id])
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rex-empty-box">
                            <span class="rex-empty-icon"><flux:icon name="bars-3" class="size-5" /></span>
                            <h2 class="text-base font-bold text-[#0f172a]">No navigation items yet</h2>
                            <p class="mx-auto mt-2 max-w-sm text-xs leading-5 text-[#64748b]">Choose published content or add a custom link to get started.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            @if (! $editingId)
            <aside class="rex-nav-editor">
                <div class="rex-nav-editor-head">
                    <div>
                        <p class="rex-nav-editor-kicker">Editor</p>
                        <h2>{{ $editingId ? 'Edit navigation item' : 'Add navigation item' }}</h2>
                        <p>Add an internal CMS link or a safe custom URL.</p>
                    </div>
                    <span class="rex-nav-editor-icon"><flux:icon name="link" class="size-5" /></span>
                </div>

                <form wire:submit="save" class="rex-nav-form" x-on:input="hasUnsavedChanges = true" x-on:change="hasUnsavedChanges = true">
                    <div class="rex-nav-field">
                        <label for="primary-nav-label">Label</label>
                        <input id="primary-nav-label" wire:model="label" placeholder="Navigation label" />
                        @error('label')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="rex-nav-field">
                        <label for="primary-nav-type">Type</label>
                        <select id="primary-nav-type" wire:model.live="type">
                            <option value="page">Internal Page</option>
                            <option value="service">Service</option>
                            <option value="project">Project</option>
                            <option value="blog">Blog</option>
                            <option value="system">System Link</option>
                            <option value="custom">Custom URL</option>
                        </select>
                    </div>

                    @if (in_array($type, ['custom', 'system'], true))
                        <div class="rex-nav-field">
                            <label for="primary-nav-url">{{ $type === 'system' ? 'System link' : 'URL' }}</label>
                            @if ($type === 'system')
                                <select id="primary-nav-url" wire:model="url">
                                    <option value="">Choose system route</option>
                                    @foreach ($options['system'] ?? [] as $route)
                                        <option value="{{ $route->id }}">{{ $route->title }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="primary-nav-url" wire:model="url" placeholder="https://example.com" />
                            @endif
                            @error('url')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div class="rex-nav-field">
                            <label for="primary-nav-target">Published Content</label>
                            <select id="primary-nav-target" wire:model="targetId">
                                <option value="">Choose content</option>
                                @foreach ($options[$type] ?? [] as $option)
                                    <option value="{{ $option->id }}">{{ in_array($type, ['service', 'project']) ? $option->name : $option->title }}</option>
                                @endforeach
                            </select>
                            @error('targetId')<p class="cms-error">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div class="rex-nav-field">
                        <label for="primary-nav-parent">Parent Item</label>
                        <select id="primary-nav-parent" wire:model="parentId">
                            <option value="">Top level</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                            @endforeach
                        </select>
                        @error('parentId')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="rex-nav-two-up">
                        <div class="rex-nav-field">
                            <label for="primary-nav-window">Open In</label>
                            <select id="primary-nav-window" wire:model="target">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>

                        <div class="rex-nav-toggle-row">
                            <div>
                                <label for="primary-nav-enabled" class="text-xs font-semibold text-[#1e293b]">Enabled</label>
                                <p class="rex-nav-toggle-help">Show on site</p>
                            </div>
                            <label class="rex-nav-toggle" for="primary-nav-enabled">
                                <input id="primary-nav-enabled" type="checkbox" wire:model="enabled" />
                                <span class="rex-nav-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="rex-nav-actions-bar">
                        <button type="button" wire:click="resetForm" class="rex-nav-action-button rex-nav-action-button--secondary">Clear</button>
                        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update item' : 'Save item' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </aside>
            @endif
            </div>
        </div>
    @endif

    @if ($showCreateMenuModal)
        <div class="rex-modal-overlay" role="presentation">
            <section class="rex-modal-card" role="dialog" aria-modal="true" aria-labelledby="create-menu-title">
                <h2 id="create-menu-title" class="rex-modal-title">Create menu</h2>
                <p class="rex-modal-copy">Create a new menu to organize a separate set of website links. Existing menus and location assignments will remain unchanged.</p>
                <form wire:submit="createMenu">
                    <div class="rex-nav-field">
                        <label for="new-menu-name">Menu name</label>
                        <input id="new-menu-name" type="text" wire:model="newMenuName" placeholder="e.g. Marketing links" autofocus />
                        @error('newMenuName')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showCreateMenuModal', false)" class="rex-nav-action-button rex-nav-action-button--secondary">Cancel</button>
                        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createMenu">Create menu</span>
                            <span wire:loading wire:target="createMenu">Creating...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    @if ($showRenameMenuModal)
        <div class="rex-modal-overlay" role="presentation">
            <section class="rex-modal-card" role="dialog" aria-modal="true" aria-labelledby="rename-menu-title">
                <h2 id="rename-menu-title" class="rex-modal-title">Rename menu</h2>
                <p class="rex-modal-copy">Existing menu items and theme location assignments will be preserved.</p>
                <form wire:submit="renameMenu">
                    <div class="rex-nav-field">
                        <label for="rename-menu-name">Menu name</label>
                        <input id="rename-menu-name" type="text" wire:model="renameMenuName" autofocus />
                        @error('renameMenuName')<p class="cms-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showRenameMenuModal', false)" class="rex-nav-action-button rex-nav-action-button--secondary">Cancel</button>
                        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary" wire:loading.attr="disabled">Save name</button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    @if ($showDeleteMenuModal)
        <div class="rex-modal-overlay" role="presentation">
            <section class="rex-modal-card" role="dialog" aria-modal="true" aria-labelledby="delete-menu-title">
                <h2 id="delete-menu-title" class="rex-modal-title">Delete {{ Str::headline($menu) }} menu?</h2>
                <p class="rex-modal-copy">This removes the menu and its navigation items. Any theme locations assigned to it will be cleared. Linked pages and other CMS content will not be deleted.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showDeleteMenuModal', false)" class="rex-nav-action-button rex-nav-action-button--secondary">Cancel</button>
                    <button type="button" wire:click="deleteMenu('{{ $menu }}')" wire:loading.attr="disabled" class="rex-nav-action-button rex-nav-action-button--danger">Delete menu</button>
                </div>
            </section>
        </div>
    @endif

    <!-- ========================================================
         MODALS (Add Column / Rename Column / Delete Column)
    ========================================================= -->
    <!-- 1. Add Column Modal -->
    @if ($showAddColumnModal)
        <div class="rex-modal-overlay">
            <div class="rex-modal-card">
                <h3 class="rex-modal-title">Add Footer Column</h3>
                <p class="rex-modal-copy">Enter a column heading such as Explore, Company, Services, or Legal.</p>
                <form wire:submit="createColumn" x-on:input="hasUnsavedChanges = true">
                    <div class="rex-nav-field">
                        <label for="modal-column-name">Column Heading</label>
                        <input id="modal-column-name" wire:model="newColumnName" placeholder="e.g. Explore" autofocus />
                        @error('newColumnName')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showAddColumnModal', false)" class="rex-nav-action-button rex-nav-action-button--secondary">Cancel</button>
                        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary">Create Column</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 2. Rename Column Modal -->
    @if ($editingColumnId)
        <div class="rex-modal-overlay">
            <div class="rex-modal-card">
                <h3 class="rex-modal-title">Rename Column</h3>
                <p class="rex-modal-copy">Update the heading displayed for this column.</p>
                <form wire:submit="updateColumn" x-on:input="hasUnsavedChanges = true">
                    <div class="rex-nav-field">
                        <label for="modal-edit-column-name">Column Heading</label>
                        <input id="modal-edit-column-name" wire:model="editingColumnName" autofocus />
                        @error('editingColumnName')<p class="cms-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" wire:click="cancelEditColumn" class="rex-nav-action-button rex-nav-action-button--secondary">Cancel</button>
                        <button type="submit" class="rex-nav-action-button rex-nav-action-button--primary">Update Heading</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 3. Delete Column Confirmation Modal -->
    @if ($showDeleteColumnModal)
        <div class="rex-modal-overlay">
            <div class="rex-modal-card">
                <h3 class="rex-modal-title">Delete Footer Column</h3>
                <p class="rex-modal-copy">
                    This column contains links. You can move the links to another column before deleting, or delete the column's footer links.
                    <span class="mt-1 block font-semibold text-[#0f172a]">Original CMS content (Pages, Services, Articles) will never be deleted.</span>
                </p>
                <div class="rex-nav-field">
                    <label for="reassign-col">Move links to (Optional):</label>
                    <select id="reassign-col" wire:model="reassignColumnId">
                        <option value="">-- Delete column links --</option>
                        @foreach ($footerColumns as $otherCol)
                            @if ($otherCol->id !== $deletingColumnId)
                                <option value="{{ $otherCol->id }}">{{ $otherCol->label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showDeleteColumnModal', false)" class="rex-nav-action-button rex-nav-action-button--secondary">Cancel</button>
                    <button type="button" wire:click="deleteColumn" class="rex-nav-action-button rex-nav-action-button--danger">Confirm Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
