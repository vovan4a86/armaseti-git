<section class="s-req lazy" data-bg="/static/images/common/request-bg.jpg">
    <form class="s-req__container container" action="{{ route('ajax.send-request') }}">
        <div class="s-req__head">
            <div class="title title--white">Отправить заявку</div>
        </div>
        <div class="s-req__body">
            <div class="s-req__col">
                <div class="s-req__fields">
                    <input class="s-req__field" type="text" name="name" placeholder="Имя">
                    <input class="s-req__field" type="tel" name="phone" placeholder="Номер телефона">
                    <input class="s-req__field" type="text" name="email" placeholder="Email *" required>
                </div>
            </div>
            <div class="s-req__col">
                <div class="s-req__fields">
                    <textarea class="s-req__field" name="message" placeholder="Сообщение" rows="4"></textarea>
                </div>
            </div>
            <div class="s-req__col">
                <div class="s-req__fields">
                    <div class="s-req__upload">
                        <label class="upload">
                            <span class="upload__name">Прикрепить файл</span>
                            <input class="upload__input" type="file" name="file"
                                   accept=".jpg, .jpeg, .png, .pdf, .doc, .docs, .xls, .xlsx">
                        </label>
                    </div>
                    <div class="s-req__upload">
                        <label class="upload">
                            <span class="upload__name">Прикрепить реквизиты</span>
                            <input class="upload__input" type="file" name="details"
                                   accept=".jpg, .jpeg, .png, .pdf, .doc, .docs, .xls, .xlsx">
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="s-req__body">
            <div class="s-req__col">
                <label class="checkbox">
                    <input class="checkbox__input" type="checkbox" checked required>
                    <span class="checkbox__box"></span>
                    <span class="checkbox__policy">* Нажимая кнопку вы соглашаетесь на обработку
								<a href="{{ route('policy') }}" target="_blank">персональных данных</a>
							</span>
                </label>
            </div>
            <div class="s-req__col">
                <button class="btn btn--accent btn--wide btn-reset" aria-label="Отправить">
                    <span class="btn__label">Отправить</span>
                </button>
            </div>
        </div>
    </form>
</section>
