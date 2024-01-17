<div class="header__action" data-header-compare>
    <a class="h-action" href="{{ route('compare') }}" title="Сравнение">
        <span class="icon iconify" data-icon="gg:menu-left-alt" data-rotate="270deg"
              data-width="22"></span>
        @if(count(Session::get('compare', [])) > 0)
            <span class="h-action__counter">{{ count(Session::get('compare')) }}</span>
        @endif
    </a>
</div>
