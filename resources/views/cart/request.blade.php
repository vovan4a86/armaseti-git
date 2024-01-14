<div class="b-cart__order">
    <div class="order">
        <div class="order__head">
            <div class="title">Отправить заявку</div>
        </div>
        <div class="order__grid">
            <div class="order__item">
                <div class="order__fields">
                    <input class="input" type="text" name="name" placeholder="Имя" />
                    <input class="input" type="tel" name="phone" placeholder="Номер телефона" />
                    <input class="input" type="text" name="email" placeholder="Email *" required="required" />
                </div>
            </div>
            <div class="order__item">
                <div class="order__fields">
                    <textarea class="order__text input" name="message" placeholder="Сообщение" rows="6"></textarea>
                </div>
            </div>
            <div class="order__item">
                <div class="order__fields">
                    <label class="upload upload--black">
                        <span class="upload__name">Прикрепить файл</span>
                        <input class="upload__input" type="file" name="file"
                               accept=".jpg, .jpeg, .png, .pdf, .doc, .docs, .xls, .xlsx" />
                    </label>
                    <label class="upload upload--black">
                        <span class="upload__name">Прикрепить реквизиты</span>
                        <input class="upload__input" type="file" name="details"
                               accept=".jpg, .jpeg, .png, .pdf, .doc, .docs, .xls, .xlsx" />
                    </label>
                </div>
            </div>
            <div class="order__item">
                <button class="btn btn--accent btn--wide btn-reset" type="submit" aria-label="Оставить заявку">
                    <span class="btn__label">Оставить заявку</span>
                </button>
                <label class="checkbox checkbox--accent">
                    <input class="checkbox__input" type="checkbox" checked="checked" required="required" />
                    <span class="checkbox__box"></span>
                    <span class="checkbox__policy">* Нажимая кнопку вы соглашаетесь на обработку
                        <a href="{{ route('policy') }}" target="_blank">персональных данных</a>
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>
