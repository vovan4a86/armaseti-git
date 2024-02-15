@if ($catalog->id)
    <div class="row">
        <div class="col-sm-6">
            <h4>Преимущества продукции
                <i class="fa fa-question-circle fa-quest"></i>
                <img class="img-feature" src="/adminlte/questions/catalog_feat.png" alt="question_3">
            </h4>
            <div class="form-group">
                <label class="btn btn-success">
                    <input id="offer_imag_upload" type="file" multiple
                           accept=".svg,.png"
                           data-url="{{ route('admin.catalog.feature-upload', $catalog->id) }}"
                           style="display:none;" onchange="catalogFeatureUpload(this, event)">
                    Загрузить иконки
                </label>
                <p>Формат: .svg,.png (40x40)</p>
            </div>
            <div class="box box-solid">
                <div class="box-body">
                    <table class="table table-striped table-v-middle">
                        <tbody id="features-list">
                        @foreach ($features as $item)
                            @include('admin::catalog.tabs_catalog.feature_row')
                        @endforeach
                        </tbody>
                    </table>

                    <script type="text/javascript">
                        $("#features-list").sortable({
                            update: function (event, ui) {
                                var url = "{{ route('admin.catalog.feature-reorder') }}";
                                var data = {};
                                data.sorted = ui.item.closest('#features-list').sortable("toArray", {attribute: 'data-id'});
                                sendAjax(url, data);
                            }
                        }).disableSelection();
                    </script>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <h4>Преимущества покупки
                <i class="fa fa-question-circle fa-quest"></i>
                <img class="img-benefit" src="/adminlte/questions/catalog_benefit.png" alt="question_4">
            </h4>
            <div class="form-group">
                <label class="btn btn-success">
                    <input id="offer_imag_upload" type="file" multiple
                           accept=".svg,.png"
                           data-url="{{ route('admin.catalog.benefit-upload', $catalog->id) }}"
                           style="display:none;" onchange="catalogBenefitUpload(this, event)">
                    Загрузить иконки
                </label>
                <p>Формат: .svg,.png (20x20)</p>
            </div>
            <div class="box box-solid">
                <div class="box-body">
                    <table class="table table-striped table-v-middle">
                        <tbody id="benefits-list">
                            @foreach ($benefits as $item)
                                @include('admin::catalog.tabs_catalog.benefit_row')
                            @endforeach
                        </tbody>
                    </table>

                    <script type="text/javascript">
                        $("#benefits-list").sortable({
                            update: function (event, ui) {
                                const url = "{{ route('admin.catalog.benefit-reorder') }}";
                                const data = {};
                                data.sorted = ui.item.closest('#benefits-list').sortable("toArray", {attribute: 'data-id'});
                                sendAjax(url, data);
                            }
                        }).disableSelection();
                    </script>
                </div>
            </div>
        </div>
    </div>
@else
    <p>Преимущества можно добавить после сохранения каталога.</p>
@endif
