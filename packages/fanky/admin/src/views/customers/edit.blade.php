<form action="{{ route('admin.customers.save') }}" onsubmit="return customerSave(this, event)">
	<input type="hidden" name="id" value="{{ $customer->id }}">

	<div class="form-group">
		<label for="user-email">E-mail</label>
		<input id="user-email" class="form-control"
			   type="text" name="email" value="{{ $customer->email }}">
	</div>

	<div class="form-group">
		<label for="user-name">Имя</label>
		<input id="user-name" class="form-control"
			   type="text" name="name" value="{{ $customer->name }}">
	</div>

	<div class="form-group">
		<label for="user-phone">E-mail</label>
		<input id="user-phone" class="form-control"
			   type="text" name="phone" value="{{ $customer->phone }}">
	</div>

	<div class="form-group">
		<input type="file" name="details" value=""
			   accept=".pdf,.xls,.xlsx,.doc,docx"
			   onchange="return detailsAttache(this, event)">
		<div id="details">
			@if ($customer->details)
				<a href="{{ $customer->file_src }}">{{ $customer->details }}</a>
				<a class="images_del"
				   href="{{ route('admin.customers.deleteDetails', $customer->id) }}"
				   onclick="return detailsDelete(this, event)">
					<span class="glyphicon glyphicon-trash text-red"></span>
				</a>
			@else
				<p class="text-yellow">Реквизиты не загружены.</p>
			@endif
		</div>
	</div>

	<button class="btn btn-primary" type="submit">Сохранить</button>
</form>
