<section class="s-callback lazy" data-bg="/static/images/common/s-callback-decor.svg">
    <div class="s-callback__container container">
        <div class="s-callback__grid">
            <form class="s-callback__form" action="{{ route('ajax.feedback') }}" onsubmit="sendCallback(this, event)">
                <div class="s-callback__form-body">
                    <div class="page-title oh">
                        <span data-aos="fade-down" data-aos-duration="900">Обратная связь</span>
                    </div>
                    <div class="field" data-aos="fade-down" data-aos-delay="350">
                        <input class="field__input" type="text" name="name" required>
                        <span class="field__highlight"></span>
                        <span class="field__bar"></span>
                        <label class="field__label">Ваше имя</label>
                    </div>
                    <div class="field" data-aos="fade-down" data-aos-delay="450">
                        <input class="field__input" type="text" name="email" required>
                        <span class="field__highlight"></span>
                        <span class="field__bar"></span>
                        <label class="field__label">Email</label>
                    </div>
                    <div class="field" data-aos="fade-down" data-aos-delay="550">
                        <textarea class="field__input" name="message" required rows="1"></textarea>
                        <span class="field__highlight"></span>
                        <span class="field__bar"></span>
                        <label class="field__label">Сообщение</label>
                    </div>
                    <div class="s-callback__submit" data-aos="fade-down" data-aos-delay="650">
                        <button class="submit btn-reset" aria-label="отправить" type="submit">
                            <span>отправить</span>
                        </button>
                    </div>
                </div>
                <div class="s-callback__socials">
                    <div class="social-links social-links--accent">
                        <div class="social-links__label">social media</div>
                        @if ($vk = Settings::get('soc_vk'))
                            <a class="social-links__icon" href="{{ $vk }}" title="Люкскрафт Youtube">
                                <span class="iconify" data-icon="ant-design:youtube-filled"></span>
                            </a>
                        @endif
                        @if ($yt = Settings::get('soc_yt'))
                            <a class="social-links__icon" href="{{ $yt }}" title="Люкскрафт ВКонтакте">
                                <span class="iconify" data-icon="ion:logo-vk"></span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
            <div class="s-callback__view lazy" data-bg="/static/images/common/s-callback-bg.png"></div>
        </div>
    </div>
</section>
