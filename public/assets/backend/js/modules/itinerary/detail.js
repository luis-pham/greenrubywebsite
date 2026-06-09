let listImportantNote = window.listImportantNote;
let duration = window.duration;
let listItineraryDay = window.listItineraryDay;
let readOnly = window.readOnly;
let fileManagerUrl = window.fileManagerUrl;
let listImportantNoteImageLinkValue = window.listImportantNoteImageLinkValue;
let listImportantNoteImageLinkFull = window.listImportantNoteImageLinkFull;

function formatToHHmm(time) {
    if (!time) return '';
    return time.slice(0, 5); // "HH:mm:ss" → "HH:mm"
}

$(document).ready(function(){
    const inputImportantNoteHtml = (
        inputClass = '',
        placeholder = '',
        contentName = '',
        content = "",
        imageLinkName = '',
        imageLinkFull = "",
        imageLinkValue = ""
    ) => `
        <div class="dynamic-input-item ${inputClass}">
            ${
               !readOnly
                   ? `<input type="hidden" class="image-select" name="${imageLinkName}" data-file-manager-url="${fileManagerUrl}" data-link-full="${imageLinkFull}" value="${imageLinkValue}"/>`
                   : `<img src="${imageLinkFull}" alt="ImageNote"/>`
            }
            <div class="input-wrapper">
                ${
                    !readOnly?
                        `<textarea
                            name="${contentName}"
                            class="form-control important-note-content"
                            autocomplete="off"
                            placeholder="${placeholder}"
                            ${readOnly ? 'disabled' : ''}
                            >${content}</textarea>`
                        : `${content}`
                }
                ${
                    !readOnly ? `
                        <button type="button" class="btn-remove" title="Xóa" aria-label="Remove item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    ` : ''
                }
            </div>
        </div>
    `

    const appendImportantNoteItem = (container,content = '',imageLinkFull = "",imageLinkValue = "") => {
        const columnName = "important_note";
        const index = container.find('.dynamic-input-item').length;

        const contentName = `${columnName}[${index}][content]`;
        const imageLinkName = `${columnName}[${index}][image_link]`;
        const placeholder = container.data('placeholder') || 'Enter value...';
        const itemInputClass  = container.data('item-input-class') || '';

        container.append(inputImportantNoteHtml(itemInputClass,placeholder,contentName,content,imageLinkName,imageLinkFull,imageLinkValue));
        if(!readOnly){
            container.find('.image-select').last().imageSelect();
            container.find('.important-note-content').textEditor({
                menubar: false,
                toolbar: [
                    'bold italic underline strikethrough | backcolor forecolor | link unlink | removeformat | charmap | superscript subscript | code'
                ],
                contextmenu: 'cut copy paste',
            })
        }
    }

    $('.dynamic-input-container').on('click','.btn-remove',function(){
        const container = $(this).closest('.dynamic-input-container')
        if(container.find('.dynamic-input-item').length > 0){
            $(this).closest('.dynamic-input-item').remove();
        }
    })

    $('.btn-add-input-item').on('click',function(){
        const target = $(this).data('target');
        const container = $('#' + target);
        appendImportantNoteItem(container);
    })

    const appendItineraryDayDetail = (container,dayIdx,detailIdx,detail = null) => {
        const html = `
            <div class="item">
                <input
                    type='hidden'
                    name='itinerary_days[${dayIdx}][itinerary_day_details][${detailIdx}][id]'
                    value="${detail?.id ?? null}"
                />
                <div class="time">
                    <p class="mb-1">Time</p>
                    <input
                        name="itinerary_days[${dayIdx}][itinerary_day_details][${detailIdx}][time]"
                        class="input-clock-picker"
                        placeholder="hh:mm"
                        data-placement="right"
                        autocomplete="off"
                        ${readOnly ? 'disabled' : ''}
                        readonly
                        value="${detail ? formatToHHmm(detail.time) : "00:00"}"
                    />
                </div>
                <div class="d-flex flex-column flex-grow-1" style="gap:1rem;">
                    <input
                        type="text"
                        name="itinerary_days[${dayIdx}][itinerary_day_details][${detailIdx}][title]"
                        value="${detail?.title ?? ""}"
                        autocomplete="off"
                        ${readOnly ? 'disabled' : ''}
                        class="form-control"
                        placeholder="Tên hoạt động..."
                    />
                    <textarea
                        class="form-control"
                        name="itinerary_days[${dayIdx}][itinerary_day_details][${detailIdx}][description]"
                        autocomplete="off"
                        ${readOnly ? 'disabled' : ''}
                        rows="2"
                        placeholder="Mô tả chỉ tiết hoạt động..."
                    >${detail?.description ?? ""}</textarea>
                </div>
                ${
                    !readOnly ? `
                        <a
                            href="javascript:void(0)"
                            class="btn-remove-item"
                        >
                            <i class="fas fa-times"></i>
                        </a>
                    ` : ''
                }
            </div>
        `
        container.append(html);

        container.find('.input-clock-picker').each(function(){
            initializeClockPicker($(this));
        });
    }

    const renderItineraryDay = (container,duration,listItineraryDay) => {
        for(let i = 0; i<duration; ++i){
            const itineraryDay = listItineraryDay?.[i];
            const html = `
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Ngày ${i + 1}</h5>
                        ${
                            !readOnly ? `
                                <a
                                    href="javascript:void(0)"
                                    class="btn-add"
                                >
                                    <i class="fas fa-plus"></i>
                                </a>
                            ` : ''
                        }
                    </div>
                    <div class="card-body">
                        <div class="itinerary-day-detail-container">
                        <input
                            type='hidden'
                            name='itinerary_days[${i}][id]'
                            value="${itineraryDay?.id ?? null}"
                        />
                        </div>
                    </div>
                </div>
            `
            container.append(html);
        }
    }

    const renderItineraryDayDetail = (itineraryDayContainer) => {
        const itineraryDayDetailContainers = itineraryDayContainer.find('.itinerary-day-detail-container');
        itineraryDayDetailContainers.each(function(idx){
            const container = $(this);
            const itineraryDay = listItineraryDay[idx];

            if(itineraryDay?.itinerary_day_details?.length > 0){
                itineraryDay.itinerary_day_details.forEach((d,dIdx) => {
                    appendItineraryDayDetail(container,idx,dIdx,d);
                });
            } else {
                appendItineraryDayDetail(container,idx,0);
            }
        });
    }

    const importantNoteContainer = $('#important_note');
    const itineraryDayContainer = $('#itinerary-day-container');

    if (Array.isArray(listImportantNote)) {
        for (let i = 0; i<listImportantNote.length; i++) {
            appendImportantNoteItem(importantNoteContainer,listImportantNote[i].content,listImportantNoteImageLinkFull[i],listImportantNoteImageLinkValue[i]);
        }
    }

    $('#itineraryDurationSelect').on('change',function(e){
        const selected = e.target.value;
        itineraryDayContainer.html('');
        renderItineraryDay(itineraryDayContainer,selected,listItineraryDay);
        renderItineraryDayDetail(itineraryDayContainer);
    });

    itineraryDayContainer.on('click','.btn-add',function(){
        const card = $(this).closest('.card');
        const container = card.find('.itinerary-day-detail-container');
        const dayIdx = card.index();
        const nextDetailIdx = container.find('.item').length;

        appendItineraryDayDetail(container,dayIdx,nextDetailIdx);
    });

    itineraryDayContainer.on('click','.btn-remove-item',function(){
        $(this).closest('.item').remove();
    });

    renderItineraryDay(itineraryDayContainer,duration,listItineraryDay);
    renderItineraryDayDetail(itineraryDayContainer);
})
