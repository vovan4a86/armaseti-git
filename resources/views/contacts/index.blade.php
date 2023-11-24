@extends('template')
@section('content')
    <main>
        <section class="cont">
            <div class="cont__wrapper">
                <div class="cont__container container">
                    @include('blocks.bread')
                    <div class="cont__body" data-aos="fade-down" data-aos-duration="900">
                        <div class="cont__heading">
                            <div class="page-title oh">
                                <span data-aos="fade-down" data-aos-duration="900"
                                      data-custom-title="data-custom-title">Контакты</span>
                            </div>
                        </div>
                        <div class="cont__grid" x-data="{ isOpen: 'Главный офис' }">
                            <div class="cont__col">
                                <!--._item-->
                                <div class="cont__item" @click="isOpen = 'Главный офис'">
                                    <div class="cont__label" :class="isOpen == 'Главный офис' &amp;&amp; 'is-active'">
                                        Главный офис
                                    </div>
                                    @if($contacts = Settings::get('contacts'))
                                        <div class="cont__data faded" x-show="isOpen == 'Главный офис'"
                                             x-cloak="x-cloak">
                                            @if($contacts['phone'])
                                                <dl class="cont__data-list">
                                                    <dt class="cont__data-term">Телефон:</dt>
                                                    <dd class="cont__data-def">
                                                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $contacts['phone']) }}">{{ $contacts['phone'] }}</a>
                                                    </dd>
                                                </dl>
                                            @endif
                                            @if($contacts['email'])
                                                <dl class="cont__data-list">
                                                    <dt class="cont__data-term">Email:</dt>
                                                    <dd class="cont__data-def">
                                                        <a href="mailto:{{ $contacts['email'] }}">{{ $contacts['email'] }}</a>
                                                    </dd>
                                                </dl>
                                            @endif
                                            @if($contacts['address'])
                                                <dl class="cont__data-list">
                                                    <dt class="cont__data-term">Адрес производства:</dt>
                                                    <dd class="cont__data-def">{{ $contacts['address'] }}
                                                    </dd>
                                                </dl>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <!--._item-->

                                @foreach($company_personal as $key => $name)
                                    <div class="cont__item" @click="isOpen = '{{ $name }}'">
                                        <div class="cont__label"
                                             :class="isOpen == '{{ $name }}' &amp;&amp; 'is-active'">
                                            {{ $name }}
                                        </div>
                                        <div class="cont__data faded" x-show="isOpen == '{{ $name }}'"
                                             x-cloak="x-cloak">
                                            @if($phone = Settings::get($key)['phone'])
                                                <dl class="cont__data-list">
                                                    <dt class="cont__data-term">Телефон:</dt>
                                                    <dd class="cont__data-def">
                                                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                                                            {{ $phone }}{{ Settings::get($key)['phone_dop'] ? ', ' . Settings::get($key)['phone_dop']: '' }}
                                                        </a>
                                                    </dd>
                                                </dl>
                                            @endif
                                            @if($email = Settings::get($key)['email'])
                                                <dl class="cont__data-list">
                                                    <dt class="cont__data-term">Email:</dt>
                                                    <dd class="cont__data-def">
                                                        <a href="mailto:{{ $email }}">{{ $email }}</a>
                                                    </dd>
                                                </dl>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <div class="cont__links">
                                    @if ($vk = Settings::get('soc_vk'))
                                        <a class="cont__link" href="{{ $vk }}" title="Группа ВКонтакте"
                                           target="_blank">
                                    <span class="cont__link-icon iconify" data-icon="ei:sc-vk"
                                          data-width="32"></span>
                                        </a>
                                    @endif
                                    @if ($yt = Settings::get('soc_yt'))
                                        <a class="cont__link-icon" href="{{ $yt }}" title="Наш Youtube"
                                           target="_blank">
                                    <span class="cont__link-icon iconify" data-icon="mdi:youtube"
                                          data-width="32"></span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="cont__col">
                                <div class="cont__view faded" x-show="isOpen == 'Главный офис'" x-cloak="x-cloak">
                                    <div class="cont__map">
                                        @if(Settings::get('contacts')['address'])
                                            <div class="cont__map-badge">
                                                <div class="badge">
                                                    <div class="badge__top">
                                                        <span class="badge__pin iconify" data-icon="ooui:map-pin"
                                                              data-width="17"></span>
                                                        <span class="badge__label">Адрес производства</span>
                                                    </div>
                                                    <div class="badge__body">
                                                        <div class="badge__label">{{ Settings::get('contacts')['address'] }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <img class="cont__map-pic" src="/static/images/common/map.svg" width="783"
                                                 height="588" alt="map"/>
                                            @if(Settings::get('contacts_yandex'))
                                                <div class="cont__map-schem">
                                                    <a class="cont__map-link"
                                                       href="{{ Settings::get('contacts_yandex') }}"
                                                       target="_blank">смотреть на яндекс картах</a>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                @foreach($company_personal as $key => $name)
                                    <div class="cont__view faded" x-show="isOpen == '{{ $name }}'"
                                         x-cloak="x-cloak">
                                        @if($persons = Settings::get($key . '_personal'))
                                                <div class="user">
                                                    @foreach($persons as $person)
                                                    <div class="user__item">
                                                        @if($img = $person['photo'])
                                                        <img class="user__pic lazy"
                                                             src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                                             data-src="{{ Settings::fileSrc($person['photo']) }}" width="175"
                                                             height="226"
                                                             alt="{{ $person['name'] }}"/>
                                                        @endif
                                                        <div class="user__data">
                                                            <div class="user__info">
                                                                <div class="user__name">{{ $person['name'] }}</div>
                                                                <div class="user__job">{{ $person['job'] }}</div>
                                                            </div>
                                                            <div class="user__links">
                                                                <a class="user__link" href="tel:{{ preg_replace('/[^\d+]/', '', $person['phone']) }}">
                                                        <span class="user__link-icon iconify"
                                                              data-icon="ic:baseline-phone" data-width="14"></span>
                                                                    <span class="user__link-data">{{ $person['phone'] }}{{ $person['phone_dop'] ? ', ' . $person['phone_dop'] : '' }}</span>
                                                                </a>
                                                                <a class="user__link" href="mailto:{{ $person['email'] }}">
                                                        <span class="user__link-icon iconify"
                                                              data-icon="dashicons:email-alt" data-width="14"></span>
                                                                    <span class="user__link-data">{{ $person['email'] }}</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('blocks.callback')
    </main>
@stop
