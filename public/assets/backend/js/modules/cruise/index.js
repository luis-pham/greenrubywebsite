let itineraryMap = new Map(Object.entries(window.itineraryData))
let listItinerary = window.listItinerary;
let mapStoreUrl = new Map(Object.entries(window.listStoreUrl));
let mapDeleteUrl = new Map(Object.entries(window.listDeleteUrl));

$(document).ready(function(){
    const userInterface = new UserInterface();

    function formatDisplayTimeHHMMP(time){
        if(!time) return '';
        const [hours,minutes] = time.split(':');
        let hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour%12 || 12;
        return `${hour}:${minutes} ${ampm}`;
    }

    function convertDMYToYMD(date){
        if(!date) return '';
        const [d,m,y] = date.split('/');
        return `${y.padStart(4,'0')}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
    }

    // ─── Map helpers ────────────────────────────────────────────
    function mapAddItinerary(cruiseId, itinerary){
        const list = itineraryMap.get(cruiseId.toString()) ?? [];
        itineraryMap.set(cruiseId.toString(), [...list, itinerary]);
    }

    function mapRemoveItinerary(cruiseId, itineraryId, startAt){
        const list = itineraryMap.get(cruiseId.toString()) ?? [];
        itineraryMap.set(
            cruiseId.toString(),
            list.filter(it => !(it.id.toString() === itineraryId.toString() && it.start_at === startAt))
        );
    }
    // ────────────────────────────────────────────────────────────

    function appendFormItinerary(container, cruiseId, storeUrl, deleteUrl) {
        const html = `
            <div class="form-add-itinerary">
                <div class="row">
                    <div class="form-group col-6">
                        <label class="col-form-label">Ngày khởi hành</label>
                        <input type="text" class="form-control date-picker" name="start_at" />
                    </div>
                    <div class="form-group col-6">
                        <label class="col-form-label">Chọn hành trình</label>
                        <select class="select2" name="itinerary_id">
                            ${listItinerary.map(it => `
                                <option value="${it.id}">${it.name}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="col-6">
                        <a href="javascript:void(0)" class="btn btn-cancel btn-secondary w-100">Hủy bỏ</a>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-info btn-submit w-100">Xác nhận dòng này</button>
                    </div>
                </div>
            </div>
        `;

        container.append(html);

        const $formRow = container.find('.form-add-itinerary').last();

        initializeDatePicker($formRow.find('.date-picker'));
        initializeSelect2($formRow.find('.select2'));

        $formRow.find('.btn-cancel').on('click', function () {
            $formRow.remove();
        });

        $formRow.find('.btn-submit').on('click', function () {
            const start_at    = $formRow.find('input[name="start_at"]').val().trim();
            const itinerary_id = $formRow.find('select[name="itinerary_id"]').val();

            userInterface.showLoading();
            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: { start_at, itinerary_id },
                success: function (response) {
                    userInterface.showFlashMessageInfo(response.message || 'Thêm hành trình thành công!');

                    $formRow.remove();

                    const selectedItinerary = listItinerary.find(it => it.id.toString() === itinerary_id);
                    if (selectedItinerary) {
                        const newItinerary = {
                            ...selectedItinerary,
                            start_at: convertDMYToYMD(start_at)
                        };

                        // ← update map
                        mapAddItinerary(cruiseId, newItinerary);

                        // remove empty state if present
                        container.find('.list-empty').remove();

                        appendItinerary(container, newItinerary, cruiseId, deleteUrl);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        userInterface.showFlashMessageError(xhr.responseJSON.message);
                    } else if (xhr.status === 419) {
                        userInterface.showFlashMessageError('Phiên làm việc hết hạn. Vui lòng tải lại trang.');
                    } else {
                        userInterface.showFlashMessageError(xhr.responseJSON?.message || 'Có lỗi xảy ra khi thêm hành trình.');
                    }
                },
                complete: function(){
                    userInterface.hideLoading();
                }
            });
        });
    }

    function appendItinerary(container, itinerary, cruiseId, deleteUrl){
        const startAt = new Date(itinerary.start_at);
        const day     = String(startAt.getDate()).padStart(2,'0');
        const month   = String(startAt.getMonth() + 1).padStart(2,'0');
        const year    = startAt.getFullYear();

        const html = `
            <div class="itinerary-card">
                <div class="itinerary-date">
                    <p class="day">${day}</p>
                    <p class="month">Tháng ${month}</p>
                    <p class="year">${year}</p>
                </div>
                <div class="itinerary-info">
                    <p class="name">${itinerary.name}</p>
                    <p class="start-time">${formatDisplayTimeHHMMP(itinerary.start_time)}</p>
                </div>
                <a href="javascript:void(0)" class="btn-delete">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        `;

        container.append(html);

        const $card = container.find('.itinerary-card').last();

        $card.find('.btn-delete').on('click', function(e){
            e.preventDefault();

            if(!confirm('Bạn có chắc muốn xóa?')) return;

            userInterface.showLoading();
            $.ajax({
                url: deleteUrl,
                method: 'DELETE',
                data: {
                    itinerary_id: itinerary.id,
                    start_at: itinerary.start_at
                },
                success: function(response){
                    userInterface.showFlashMessageInfo(response.message);

                    // ← update map
                    mapRemoveItinerary(cruiseId, itinerary.id, itinerary.start_at);

                    $card.remove();

                    // show empty state if no cards left
                    const $list = $card.closest('.list-itinerary');
                    if($list.find('.itinerary-card').length === 0){
                        $list.append('<div class="list-empty">Chưa có hành trình nào</div>');
                    }
                },
                error: function(xhr){
                    userInterface.showFlashMessageError(xhr.responseJSON?.message || 'Có lỗi xảy ra khi xóa.');
                },
                complete: function(){
                    userInterface.hideLoading();
                }
            });
        });
    }

    function renderListItinerary(modal, list, cruiseId, deleteUrl){
        const $listContainer = modal.find('.list-itinerary');
        $listContainer.html('');

        if(list && list.length > 0){
            list.forEach(it => appendItinerary($listContainer, it, cruiseId, deleteUrl));
        } else {
            $listContainer.append('<div class="list-empty">Chưa có hành trình nào</div>');
        }
    }

    const $modal = $('#modal-cruise-itinerary');

    $(document).on('click', '.btn-open-itinerary-modal', function(){
        $modal.data('cruise-id', $(this).data('cruise-id'));
        $modal.modal('show');
    });

    $modal.on('shown.bs.modal', function(){
        const cruiseId    = $modal.data('cruise-id');
        const list        = itineraryMap.get(cruiseId.toString());
        const deleteUrl   = mapDeleteUrl.get(cruiseId.toString());
        renderListItinerary($modal, list, cruiseId, deleteUrl);
    });

    $modal.on('click', '.btn-add-itinerary', function(){
        const $listContainer = $modal.find('.list-itinerary');
        const cruiseId       = $modal.data('cruise-id');
        const storeUrl       = mapStoreUrl.get(cruiseId.toString());
        const deleteUrl      = mapDeleteUrl.get(cruiseId.toString());

        $listContainer.find('.list-empty').remove();

        appendFormItinerary($listContainer, cruiseId, storeUrl, deleteUrl);
    });
});
