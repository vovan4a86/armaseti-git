<span class="certificate_item">
	<img class="img-polaroid" style="cursor: zoom-in;"
		 src="{{ \Fanky\Admin\Models\Product::CERTIFICATE_PATH . $cert->image }}"
		 height="200"
		 data-image="{{ \Fanky\Admin\Models\Product::CERTIFICATE_PATH . $cert->image }}"
		 onclick="return popupImage($(this).data('image'))">
		<a class="images_del" href="{{ route('admin.catalog.productCertificateDel', [$cert->id]) }}"
           onclick="return productCertificateDel(this)">
		<span class="glyphicon glyphicon-trash"></span>
	</a>
</span>
