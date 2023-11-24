<form action="{{ route('admin.users.save') }}" onsubmit="return userSave(this)">
	<input type="hidden" name="id" value="{{ $user->id }}">

	<div class="form-group">
		<label for="user-name">Имя</label>
		<input id="user-name" class="form-control" type="text" name="name" value="{{ $user->name }}">
	</div>

	<div class="form-group">
		<label for="user-email">E-mail</label>
		<input id="user-email" class="form-control" type="text" name="email" value="{{ $user->email }}">
	</div>

	<div class="row">
		<div class="col-md-7">
			<div class="form-group">
				<label for="user-username">Login</label>
				<input id="user-username" class="form-control" type="text" name="username" value="{{ $user->username }}">
			</div>
		</div>

		<div class="col-md-5">
			<div class="form-group">
				<label for="user-role">Роль</label>
				<select id="user-role" class="form-control" name="role">
		            @foreach ($user::$roles as $roleId => $roleName)
		            	<option value="{{ $roleId }}" {{ $user->role == $roleId ? 'selected' : '' }}>{{ $roleName }}</option>
		            @endforeach
		        </select>
			</div>
		</div>
	</div>

	<div class="form-group">
		<label for="user-password">Пароль</label>
		<input id="user-password" class="form-control" type="password" name="password" value="" placeholder="{{ $user->id ? 'Изменить пароль' : '' }}">
	</div>

	<button class="btn btn-primary" type="submit">Сохранить</button>
</form>