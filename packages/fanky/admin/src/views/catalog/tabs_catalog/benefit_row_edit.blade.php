<form action="{{ route('admin.catalog.benefit-save', [$item->id]) }}"
      onsubmit="benefitSave(this, event)" style="width:600px;">
    <label for="feature">Текст</label>
    <input id="feature" class="form-control" type="text" name="benefit-text"
           value="{{ $item->text }}">
    <button class="btn btn-primary" style="margin-top: 20px;" type="submit">Сохранить</button>
</form>
