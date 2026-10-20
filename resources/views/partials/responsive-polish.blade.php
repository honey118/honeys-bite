<style>
    :root {
        color-scheme: light;
        --bakery-cream: #F5EEE3;
        --bakery-espresso: #563B2D;
        --bakery-caramel: #A86F43;
        --bakery-ivory: #FFFDF9;
        --bakery-beige: #E8D8C5;
        --bakery-muted: #6E6258;
        --bakery-border: #DCCAB5;
        --bakery-danger: #963C32;
        --bakery-success: #385332;
        --bakery-serif: Georgia, "Times New Roman", serif;
        --bakery-sans: "Instrument Sans", "Segoe UI", sans-serif;
    }

    html {
        max-width: 100%;
        overflow-x: clip;
        scroll-behavior: smooth;
    }

    body {
        min-width: 0;
        max-width: 100%;
        color: var(--bakery-espresso);
        font-family: var(--bakery-sans);
        font-size: 15px;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    main,
    section,
    article,
    form,
    fieldset {
        min-width: 0;
    }

    h1,
    h2,
    h3 {
        color: var(--bakery-espresso);
        font-family: var(--bakery-serif);
        font-weight: 400;
        text-wrap: pretty;
    }

    h1 {
        font-size: clamp(1.9rem, 4vw, 2.65rem);
        letter-spacing: -0.025em;
        line-height: 1.15;
    }

    h2 {
        line-height: 1.25;
    }

    .hero h1 {
        font-size: clamp(2.5rem, 6.8vw, 4.25rem);
        line-height: 1.08;
    }

    img,
    video,
    svg {
        max-width: 100%;
    }

    input,
    textarea,
    select,
    button {
        max-width: 100%;
        font: inherit;
    }

    input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="submit"]):not([type="button"]),
    textarea,
    select {
        width: 100%;
        min-height: 46px;
        padding: 11px 13px;
        border: 1px solid var(--bakery-border);
        border-radius: 8px;
        background-color: var(--bakery-ivory);
        color: var(--bakery-espresso);
        font-size: 14px;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    textarea {
        min-height: 112px;
        resize: vertical;
        overflow-wrap: anywhere;
    }

    input[type="file"] {
        width: 100%;
        min-height: 46px;
        padding: 7px;
        border: 1px solid var(--bakery-border);
        border-radius: 8px;
        background: var(--bakery-ivory);
        color: var(--bakery-muted);
        font-size: 13px;
    }

    input[type="file"]::file-selector-button {
        min-height: 30px;
        margin-right: 10px;
        padding: 4px 10px;
        border: 1px solid var(--bakery-border);
        border-radius: 6px;
        background: var(--bakery-cream);
        color: var(--bakery-espresso);
        font: inherit;
        cursor: pointer;
    }

    input[type="checkbox"],
    input[type="radio"] {
        accent-color: var(--bakery-caramel);
    }

    input:focus-visible,
    textarea:focus-visible,
    select:focus-visible {
        border-color: var(--bakery-caramel);
        outline: 3px solid rgb(168 111 67 / 20%);
        outline-offset: 2px;
        box-shadow: none;
    }

    input[aria-invalid="true"],
    textarea[aria-invalid="true"],
    select[aria-invalid="true"] {
        border-color: var(--bakery-danger);
    }

    input::placeholder,
    textarea::placeholder {
        color: #776b60;
        opacity: 1;
    }

    label,
    legend {
        color: #49382D;
        font-size: 13px;
        font-weight: 650;
    }

    a:link,
    a:visited {
        color: #744B30;
        text-underline-offset: 3px;
        transition: color 150ms ease, text-decoration-color 150ms ease;
    }

    a:hover {
        color: #8B542D;
    }

    a:active {
        color: var(--bakery-espresso);
    }

    a:focus-visible,
    button:focus-visible,
    input:focus-visible,
    textarea:focus-visible,
    select:focus-visible,
    [tabindex="0"]:focus-visible {
        outline: 3px solid var(--bakery-caramel);
        outline-offset: 3px;
    }

    .topbar {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgb(232 216 197 / 28%);
        background: var(--bakery-espresso);
        box-shadow: 0 5px 18px rgb(45 30 22 / 10%);
    }

    .topbar nav {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: clamp(6px, 1.4vw, 14px);
        min-width: 0;
    }

    .topbar a:not(.brand),
    .topbar nav button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
    }

    .topbar .brand:link,
    .topbar .brand:visited {
        color: #FFF8EE;
        text-decoration: none;
    }

    .topbar a:not(.brand):link,
    .topbar a:not(.brand):visited {
        padding: 7px 10px;
        border-radius: 7px;
        color: #F5EEE3;
        font-size: 13px;
        text-decoration: none;
    }

    .topbar a:not(.brand):hover,
    .topbar a:not(.brand):active,
    .topbar a:not(.brand)[aria-current="page"] {
        background: rgb(255 253 249 / 12%);
        color: #FFFDF9;
        text-decoration: underline;
    }

    .topbar a:not(.brand):focus-visible {
        color: #FFFDF9;
        outline-color: #E8D8C5;
        text-decoration: underline;
    }

    .topbar nav button {
        padding: 7px 10px;
        border: 1px solid transparent;
        border-radius: 7px;
        background: transparent;
        color: #F5EEE3;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 150ms ease, color 150ms ease;
    }

    .topbar nav button:hover {
        background: rgb(255 253 249 / 12%);
        color: #FFFDF9;
    }

    .topbar nav button:active {
        background: rgb(255 253 249 / 20%);
    }

    .topbar nav form {
        display: inline-flex;
        align-items: center;
        margin: 0;
    }

    .page {
        padding-top: clamp(28px, 5vw, 48px);
        padding-bottom: clamp(48px, 7vw, 72px);
    }

    .eyebrow {
        color: #875A38;
        font-size: 11px;
        letter-spacing: 0.14em;
    }

    .intro,
    .intro-copy,
    .copy,
    .muted,
    .field-hint,
    .hint,
    .count,
    .label,
    .description,
    .address,
    .review-body,
    .note {
        color: var(--bakery-muted);
    }

    .panel,
    .card,
    .product,
    .form-shell,
    .summary,
    .form-panel,
    .auth-layout,
    .detail,
    .order,
    .empty {
        border: 1px solid var(--bakery-beige);
        border-radius: 12px;
        background: var(--bakery-ivory);
        box-shadow: 0 8px 24px rgb(71 48 33 / 5%);
    }

    .panel,
    .form-shell,
    .card,
    .detail {
        padding: clamp(20px, 3.5vw, 30px);
    }

    .auth-layout {
        border-radius: 14px;
    }

    .auth-layout > .form-panel {
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        padding: clamp(30px, 6vw, 68px);
    }

    .product {
        overflow: hidden;
        transition: transform 180ms ease, box-shadow 180ms ease;
    }

    .product:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgb(71 48 33 / 10%);
    }

    .product-photo,
    .photo {
        display: block;
        object-fit: cover;
        background: var(--bakery-beige);
    }

    .product-photo {
        border-radius: 10px 10px 0 0;
    }

    .photo {
        border-radius: 9px;
    }

    .photo-empty {
        color: #805638;
        background: linear-gradient(145deg, #EFE2D2, #F8F1E7);
    }

    .thumb {
        border-radius: 6px;
        object-fit: cover;
    }

    .product-body {
        padding: clamp(16px, 2vw, 22px);
    }

    .product h3,
    .section-title,
    .catalog-head h2 {
        color: var(--bakery-espresso);
    }

    .price,
    .order-total,
    .value {
        color: #744B30;
    }

    .product-action,
    .button,
    .submit-button,
    .review-submit,
    .archive-button,
    button[type="submit"]:not(.button):not(.submit-button):not(.review-submit):not(.archive-button) {
        min-height: 44px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-family: var(--bakery-sans);
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease, transform 150ms ease;
    }

    .button,
    .submit-button,
    .review-submit,
    .archive-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
    }

    .product-action {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
    }

    .product-action,
    .button:link,
    .button:visited,
    .submit-button,
    .review-submit,
    button[type="submit"]:not(.button):not(.submit-button):not(.review-submit):not(.archive-button) {
        background: var(--bakery-espresso);
        color: #FFFDF9;
    }

    .product-action:hover,
    .button:hover,
    .submit-button:hover,
    .review-submit:hover,
    button[type="submit"]:not(.button):not(.submit-button):not(.review-submit):not(.archive-button):hover {
        background: #704B35;
        color: #FFFDF9;
        transform: translateY(-1px);
    }

    .product-action:active,
    .button:active,
    .submit-button:active,
    .review-submit:active,
    .archive-button:active {
        transform: translateY(0);
    }

    .button-secondary {
        border-color: var(--bakery-caramel);
        background: transparent;
        color: var(--bakery-espresso);
    }

    .button-secondary:link,
    .button-secondary:visited {
        border-color: var(--bakery-caramel);
        background: transparent;
        color: var(--bakery-espresso);
    }

    .button-secondary:hover {
        background: #F3E6D8;
        color: var(--bakery-espresso);
    }

    .button-danger,
    .archive-button {
        background: #963C32;
        color: #FFFDF9;
    }

    .button-danger:link,
    .button-danger:visited {
        background: #963C32;
        color: #FFFDF9;
    }

    .button-danger:hover,
    .archive-button:hover {
        background: #7D3028;
        color: #FFFDF9;
    }

    .text-link,
    .link,
    .back {
        color: #744B30;
        font-weight: 700;
    }

    .product-action:link,
    .product-action:visited {
        color: #FFFDF9;
    }

    .form-grid,
    .form-field,
    .field {
        min-width: 0;
    }

    .form-grid {
        row-gap: 22px;
    }

    .radio-group {
        display: grid;
        gap: 10px;
    }

    .radio-option {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: var(--bakery-espresso);
    }

    .radio-option input {
        flex: 0 0 auto;
        margin-top: 3px;
    }

    .radio-option label {
        display: inline;
        margin: 0;
        color: var(--bakery-espresso);
        font-weight: 400;
    }

    .field-error,
    .error {
        color: var(--bakery-danger);
        font-size: 13px;
        line-height: 1.45;
    }

    .error-summary,
    .error {
        padding: 12px 14px;
        border: 1px solid #E7BFB7;
        border-radius: 8px;
        background: #FFF1ED;
    }

    .field-error {
        margin: 7px 0 0;
    }

    .flash,
    .confirmation {
        border: 1px solid #C6D4B9;
        border-radius: 8px;
        background: #F1F7ED;
        color: var(--bakery-success);
    }

    .status,
    .tag {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 5px 10px;
        border: 1px solid #E6D3BE;
        border-radius: 999px;
        background: #F5E9DC;
        color: #714A2D;
        font-size: 12px;
        font-weight: 650;
        line-height: 1.25;
    }

    .status-order,
    .tag-order {
        border-color: #E6C8AA;
        background: #F8EBDD;
        color: #74451F;
    }

    .status-payment,
    .tag-payment {
        border-color: #C8D8BD;
        background: #EEF4E9;
        color: #385332;
    }

    .status + .status {
        margin-left: 6px;
    }

    .status-archived,
    .availability-out {
        border-color: #E7BFB7;
        background: #FFF1ED;
        color: #8C352D;
    }

    .availability {
        color: #48603F;
        font-weight: 650;
    }

    .table-wrap {
        max-width: 100%;
        overflow-x: auto;
        border: 1px solid var(--bakery-beige);
        border-radius: 12px;
        background: var(--bakery-ivory);
        overscroll-behavior-inline: contain;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: var(--bakery-espresso);
        text-align: left;
    }

    th,
    td {
        padding: 14px 16px;
        border-bottom: 1px solid #EFE5D9;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    th {
        background: #F7F0E6;
        color: #6D5745;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    tbody tr:last-child td {
        border-bottom: 0;
    }

    tbody tr:hover {
        background: #FFFCF7;
    }

    .table-wrap td,
    .table-wrap th {
        overflow-wrap: anywhere;
    }

    .pagination {
        margin-top: 24px;
    }

    .pagination nav {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        min-width: 40px;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border: 1px solid var(--bakery-beige);
        border-radius: 7px;
        background: var(--bakery-ivory);
        text-decoration: none;
    }

    .pagination [aria-current="page"] span {
        border-color: var(--bakery-espresso);
        background: var(--bakery-espresso);
        color: #FFFDF9;
    }

    .field-error,
    .field-hint,
    .error-summary,
    .flash,
    .confirmation,
    .status,
    .tag,
    .empty {
        overflow-wrap: anywhere;
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            scroll-behavior: auto !important;
            transition-duration: 0.01ms !important;
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
        }
    }

    @media (max-width: 760px) {
        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .topbar {
            align-items: flex-start;
        }

        .topbar nav {
            width: 100%;
        }

        .auth-layout > .form-panel {
            padding: 32px 24px 36px;
        }

        .form-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .form-field-wide,
        .field-wide {
            grid-column: auto;
        }

        .page-head {
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 14px;
        }

        .actions,
        .meta,
        .product-foot,
        .review-head,
        .item-row,
        .total-row {
            flex-wrap: wrap;
        }

        .totals {
            width: 100%;
            margin-left: 0;
        }

        .text-link {
            margin-left: 0;
        }

        .pagination nav {
            justify-content: flex-start;
        }

        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .stats {
            grid-template-columns: minmax(0, 1fr);
        }

        .actions {
            align-items: stretch;
        }

        .actions > .button,
        .actions > form,
        .actions > form .button {
            width: 100%;
        }
    }

    @media (max-width: 380px) {
        body {
            min-width: 0;
        }

        .page,
        .catalog {
            padding-right: 12px;
            padding-left: 12px;
        }

        .panel,
        .form-shell,
        .summary,
        .form-panel {
            padding-right: 16px;
            padding-left: 16px;
        }

        .topbar {
            padding-right: 12px;
            padding-left: 12px;
        }
    }
</style>
