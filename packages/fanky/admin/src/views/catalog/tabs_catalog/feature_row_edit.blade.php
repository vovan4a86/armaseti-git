<form action="{{ route('admin.catalog.feature-save', [$item->id]) }}"
      onsubmit="featureSave(this, event)" style="width:600px;">
    <label for="feature">Текст</label>
    <input id="feature" class="form-control" type="text" name="feature-text"
           value="{{ $item->text }}">
    <button class="btn btn-primary" style="margin-top: 20px;" type="submit">Сохранить</button>
</form>
