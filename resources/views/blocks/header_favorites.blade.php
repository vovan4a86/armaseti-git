<div class="header__action" data-header-favorites>
    <a class="h-action" href="{{ route('favorites') }}" title="Избранное">
        <span class="icon iconify" data-icon="lucide:heart" data-width="22"></span>
        @if(count(Session::get('favorites', [])) > 0)
            <span class="h-action__counter">{{ count(Session::get('favorites')) }}</span>
        @endif
    </a>
</div>
