<style>
    .modal.fade {
        display: none !important;
    }
    .modal.fade.show {
        display: block !important;
    }
    .modal-header .close,
    .modal-header .close span {
        color: #000 !important;
        opacity: 1 !important;
    }
    #selected-amenities {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    #selected-amenities .amenity-pill {
        box-sizing: border-box;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.75rem 2rem 0.75rem 1rem;
        position: relative;
        transition: border-color 0.2s, background-color 0.2s;
        min-height: 3rem;
        display: flex;
        align-items: center;
    }
    #selected-amenities .amenity-pill .amenity-pill-remove {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 1;
    }
    #selected-amenities .amenity-pill:hover .amenity-pill-remove {
        opacity: 1;
    }
    #selected-audiences {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    #selected-audiences .audience-pill {
        box-sizing: border-box;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.75rem 2rem 0.75rem 1rem;
        position: relative;
        transition: border-color 0.2s, background-color 0.2s;
        min-height: 3rem;
        display: flex;
        align-items: center;
    }
    #selected-audiences .audience-pill .audience-pill-remove {
        position: absolute;
        top: 0.25rem;
        right: 0.25rem;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 1;
    }
    #selected-audiences .audience-pill:hover .audience-pill-remove {
        opacity: 1;
    }
    #selected-rooms .room-pill {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 0.75rem 2rem 0.75rem 1rem;
        margin-bottom: 0.5rem;
        position: relative;
    }
    #selected-rooms .room-title-display,
    #selected-rooms .room-desc-display {
        cursor: text;
        display: block;
        min-height: 1.25em;
    }
    #selected-rooms .room-desc-display {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    #selected-rooms .room-title-display.placeholder,
    #selected-rooms .room-desc-display.placeholder {
        color: #adb5bd;
    }
    #selected-rooms .room-title-edit,
    #selected-rooms .room-desc-edit {
        width: 100%;
        margin-top: 0.25rem;
    }
    #selected-rooms .room-title-edit {
        margin-top: 0;
    }
    #selected-audiences .audience-name-display {
        cursor: text;
        display: block;
        min-height: 1.25em;
    }
    #selected-audiences .audience-name-display.placeholder {
        color: #adb5bd;
    }
    #selected-audiences .audience-name-edit {
        width: 100%;
    }
</style>
