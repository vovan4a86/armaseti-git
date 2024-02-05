<form action="{{ route('admin.customers.save') }}" onsubmit="return customerSave(this)">
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


	<button class="btn btn-primary" type="submit">Сохранить</button>
</form>
