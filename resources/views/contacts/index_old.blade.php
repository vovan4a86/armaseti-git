@extends('template')
@section('content')
    <main>
        <section class="contacts">
            <div class="contacts__wrapper">
                <div class="contacts__container container">
                    @include('blocks.bread')
                    <div class="contacts__body" data-aos="fade-down" data-aos-duration="900">
                        <div class="contacts__info">
                            <div class="contacts__heading">
                                <div class="page-title oh">
                                    <span data-aos="fade-down" data-aos-duration="900">Контакты</span>
                                </div>
                            </div>
                            @if($contacts = Settings::get('contacts'))
                                <div class="contacts__data">
                                    @if($contacts['phone'])
                                        <dl class="contacts__data-list">
                                            <dt class="contacts__data-term">Телефон:</dt>
                                            <dd class="contacts__data-def">
                                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $contacts['phone']) }}">{{ $contacts['phone'] }}</a>
                                            </dd>
                                        </dl>
                                    @endif
                                    @if($contacts['email'])
                                        <dl class="contacts__data-list">
                                            <dt class="contacts__data-term">Email:</dt>
                                            <dd class="contacts__data-def">
                                                <a href="mailto:{{ $contacts['email'] }}">{{ $contacts['email'] }}</a>
                                            </dd>
                                        </dl>
                                    @endif
                                    @if($contacts['address'])
                                        <dl class="contacts__data-list">
                                            <dt class="contacts__data-term">Адрес производства:</dt>
                                            <dd class="contacts__data-def">{{ $contacts['address'] }}</dd>
                                        </dl>
                                    @endif
                                </div>
                            @endif
                            <div class="contacts__links">
                                @if ($vk = Settings::get('soc_vk'))
                                    <a class="contacts__link" href="{{ $vk }}" title="Группа ВКонтакте"
                                       target="_blank">
                                    <span class="contacts__link-icon iconify" data-icon="ei:sc-vk"
                                          data-width="32"></span>
                                    </a>
                                @endif
                                @if ($yt = Settings::get('soc_yt'))
                                    <a class="contacts__link" href="{{ $yt }}" title="Наш Youtube"
                                       target="_blank">
                                    <span class="contacts__link-icon iconify" data-icon="mdi:youtube"
                                          data-width="32"></span>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="contacts__map">
                            @if(Settings::get('contacts')['address'])
                                <div class="contacts__map-badge">
                                    <div class="badge">
                                        <div class="badge__top">
                                        <span class="badge__pin iconify" data-icon="ooui:map-pin"
                                              data-width="17"></span>
                                            <span class="badge__label">Адрес производства</span>
                                        </div>
                                        <div class="badge__body">
                                            <div class="badge__label">{{ Settings::get('contacts')['address'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <img class="contacts__map-pic" src="/static/images/common/map.svg" width="783" height="588"
                                 alt=""/>
                            @if(Settings::get('contacts_yandex'))
                                <div class="contacts__map-schem">
                                    <a class="contacts__map-link"
                                       href="{{ Settings::get('contacts_yandex') }}"
                                       target="_blank">смотреть на яндекс картах</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="contacts__sections">
                <div class="sections" x-data="{ isOpen: 1 }" data-aos="fade-down" data-aos-duration="900">
                    <div class="sections__container container">
                        <!--._data-->
                        <div class="sections__data">
                            @foreach($company_personal as $key => $name)
                                <div class="sections__item" @click="isOpen = {{ $loop->iteration }}">
                                    <div class="sections__label" :class="isOpen == 1 &amp;&amp; 'is-active'">
                                        {{ $name }}
                                    </div>
                                    <div class="sections__body faded" x-show="isOpen == {{ $loop->iteration }}"
                                         x-cloak="x-cloak">
                                        <div class="sections__list">
                                            @if($phone = Settings::get($key)['phone'])
                                                <a class="sections__value"
                                                   href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                                            <span class="sections__value-icon iconify" data-icon="ic:baseline-phone"
                                                  data-width="14"></span>
                                                    <span class="sections__value-data">{{ $phone }}{{ Settings::get($key)['phone_dop'] ? ', ' . Settings::get($key)['phone_dop']: '' }}</span>
                                                </a>
                                            @endif
                                            @if($email = Settings::get($key)['email'])
                                                <a class="sections__value" href="mailto:{{ $email }}">
                                            <span class="sections__value-icon iconify" data-icon="dashicons:email-alt"
                                                  data-width="14"></span>
                                                    <span class="sections__value-data">{{ $email }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!--._users-->
                        <div class="sections__users">
                            <!--.user-->
                            @foreach($company_personal as $key => $name)
                                <div class="user faded" x-show="isOpen == {{ $loop->iteration }}" x-cloak="x-cloak">
                                    @if($persons = Settings::get($key . '_personal'))
                                        @foreach($persons as $person)
                                            <div class="user__item">
                                                @if($img = $person['photo'])
                                                    <img class="user__pic lazy"
                                                         src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                                                         data-src="{{ Settings::fileSrc($person['photo']) }}"
                                                         width="175" height="226"
                                                         alt="{{ $person['name'] }}"/>
                                                @endif
                                                <div class="user__data">
                                                    <div class="user__info">
                                                        <div class="user__name">{{ $person['name'] }}</div>
                                                        <div class="user__job">{{ $person['job'] }}</div>
                                                    </div>
                                                    <div class="user__links">
                                                        <a class="user__link"
                                                           href="tel:{{ preg_replace('/[^\d+]/', '', $person['phone']) }}">
                                                    <span class="user__link-icon iconify" data-icon="ic:baseline-phone"
                                                          data-width="14"></span>
                                                            <span class="user__link-data">{{ $person['phone'] }}{{ $person['phone_dop'] ? ', ' . $person['phone_dop'] : '' }}</span>
                                                        </a>
                                                        <a class="user__link" href="mailto:{{ $person['email'] }}">
                                                    <span class="user__link-icon iconify"
                                                          data-icon="dashicons:email-alt"
                                                          data-width="14"></span>
                                                            <span class="user__link-data">{{ $person['email'] }}</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--class=(contactsPage && 'b-calc--small')-->
        <section class="b-calc b-calc--small">
            <div class="b-calc__container container">
                <div class="b-calc__body lazy" data-bg="/static/images/common/b-calc-decor.svg" data-aos="flip-up">
                    <div class="b-calc__title" data-aos="fade-left"
                         data-aos-delay="650">{{ Settings::get('contacts_text') }}
                    </div>
                </div>
            </div>
        </section>

        @include('blocks.callback')
    </main>
@stop
