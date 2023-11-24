<div class="char" style="display: flex; column-gap: 20px;"
     data-id="{{ $ch->id }}" data-product="{{ $product->id }}">
    <input type="text" value="{{ $ch->name }}" disabled class="form-control"
           style="max-width: 250px;">
    <div style="max-width: 400px; display: flex;">
        <input type="text" name="char" value="{{ $ch->value }}"
               class="form-control">
        <div>
        <span class="input-group-btn">
            <button class="btn btn-success btn-flat" onclick="updateCharValue(this, event)">
               <span class="glyphicon glyphicon-ok"></span>
            </button>
        </span>
        </div>
        <div>
        <span class="input-group-btn">
            <button class="btn btn-danger btn-flat" onclick="deleteChar(this, event)">
               <span class="glyphicon glyphicon-remove"></span>
            </button>
        </span>
        </div>
    </div>
</div>
