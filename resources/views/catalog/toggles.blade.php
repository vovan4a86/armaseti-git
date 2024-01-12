<div class="cat-view__toggles">
    <div class="cat-view__toggles-title">Показать вид:</div>
    <div class="cat-view__toggles-btns">
        <button class="cat-view__toggle btn-reset" type="button" :class="listView &amp;&amp; 'is-active'" @click="listView = true" aria-label="Вид списка">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" fill="none">
                <g stroke="currentColor" stroke-width="1.5">
                    <path d="M0 2h15M0 8h15M0 14h15" />
                </g>
            </svg>
        </button>
        <button class="cat-view__toggle btn-reset" type="button" :class="!listView &amp;&amp; 'is-active'" @click="listView = false" aria-label="Вид сетки">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" fill="none">
                <mask id="a" fill="#fff">
                    <rect width="6.667" height="6.667" y=".5" rx="1" />
                </mask>
                <rect width="6.667" height="6.667" y=".5" stroke="currentColor" stroke-width="3" mask="url(#a)" rx="1" />
                <mask id="b" fill="#fff">
                    <rect width="6.667" height="6.667" x="8.333" y=".5" rx="1" />
                </mask>
                <rect width="6.667" height="6.667" x="8.333" y=".5" stroke="currentColor" stroke-width="3" mask="url(#b)" rx="1" />
                <mask id="c" fill="#fff">
                    <rect width="6.667" height="6.667" x="8.333" y="8.833" rx="1" />
                </mask>
                <rect width="6.667" height="6.667" x="8.333" y="8.833" stroke="currentColor" stroke-width="3" mask="url(#c)" rx="1" />
                <mask id="d" fill="#fff">
                    <rect width="6.667" height="6.667" y="8.833" rx="1" />
                </mask>
                <rect width="6.667" height="6.667" y="8.833" stroke="currentColor" stroke-width="3" mask="url(#d)" rx="1" />
            </svg>
        </button>
    </div>
</div>
