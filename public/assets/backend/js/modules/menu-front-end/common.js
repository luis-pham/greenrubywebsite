$(document).ready(function () {
    var modalMenuId = 'modal-menu';
    var modalMenu = document.getElementById(modalMenuId) != null
        ? new coreui.Modal(document.getElementById(modalMenuId), {
            backdrop: 'static',
            keyboard: false
        })
        : null;

    var hidMenu = $('[name="menu"]');

    let kendoTreeViewOption = {
        dataSource: hidMenu.val() != '' ? getTreeviewData(JSON.parse(hidMenu.val()), null) : []
    };
    if ($('#treeview').attr('data-drag-and-drop') == 'true') {
        kendoTreeViewOption.dragAndDrop = true;
        kendoTreeViewOption.drop = function (e) {
            if ($(e.destinationNode).parents('div[data-role="treeview"]').attr('id') != 'treeview') {
                e.preventDefault();
                return;
            }
        };
        kendoTreeViewOption.dragend = function () {
            refreshTreeviewData();
        };
    }
    var treeview = $('#treeview').kendoTreeView(kendoTreeViewOption).data('kendoTreeView');

    function getTreeviewData(list, parentId) {
        let data = [];

        for (let i = 0; i < list.length; i++) {
            if (list[i].parent_id == parentId) {
                let item = {
                    text: bindTreeviewNodeText(list[i]),
                    name: list[i].name,
                    encoded: false,
                    expanded: true
                }
                let subitem = getTreeviewData(list, list[i].id);
                if (subitem.length > 0) {
                    item.items = subitem;
                }
                data.push(item);
            }
        }

        return data;
    }

    function checkTreeviewExistNode(data, id) {
        let flag = false;

        for (let i = 0; i < data.length; i++) {
            if (id == $(data[i].text).attr('data-id')) {
                flag = true;
                break;
            }

            if (data[i].items !== undefined) {
                flag = flag || checkTreeviewExistNode(data[i].items, id);
            }
        }

        return flag;
    }

    function getTreeviewList(data, parentId, level = 1) {
        let list = [];

        for (let i = 0; i < data.length; i++) {
            let obj = {
                id: $(data[i].text).attr('data-id'),
                name: $(data[i].text).attr('data-name'),
                url: $(data[i].text).attr('data-url'),
                icon: $(data[i].text).attr('data-icon'),
                target: $(data[i].text).attr('data-target'),
                ord: i + 1,
                level: level,
                parent_id: parentId
            };
            list.push(obj);

            if (data[i].items !== undefined) {
                list = list.concat(getTreeviewList(data[i].items, obj.id, level + 1));
            }
        }

        return list;
    }

    function addTreeviewNode(treeview, parent, node) {
        let data = treeview.dataSource.view();
        if (checkTreeviewExistNode(data, node.id)) {
            alert('Không thể thêm do menu đã tồn tại');
            return false;
        }

        treeview.append({
            text: bindTreeviewNodeText(node),
            name: node.name,
            encoded: false
        }, parent);

        return true;
    }

    function bindTreeviewNodeText(node) {
        let element = $('<span class="item"></span>');
        element.text(node.name);
        element.attr('data-id', node.id);
        element.attr('data-name', node.name);
        element.attr('data-url', node.url);
        element.attr('data-icon', node.icon);
        element.attr('data-target', node.target);

        return element[0].outerHTML;
    }

    function refreshTreeviewData() {
        let data = treeview.dataSource.data();
        let list = getTreeviewList(data, null);
        if (list.length == 0) {
            $('#treeview').addClass('empty');
        } else {
            $('#treeview').removeClass('empty');
        }
        hidMenu.val(JSON.stringify(list));
    }

    function bindTreeviewMenu(treeview) {
        if ($('#treeview-menu').length == 0) {
            let menuHtml =
                '<ul id="treeview-menu" class="treeview-menu">' +
                    '<li data-command="edit"><i class="fas fa-pencil"></i> Sửa</li>' +
                    '<li data-command="delete"><i class="fas fa-trash"></i> Xóa</li>' +
                '</ul>';
            $('#treeview').after(menuHtml);
        }

        $('#treeview-menu').kendoContextMenu({
            target: '#treeview',
            filter: '.k-in',
            select: function (e) {
                let button = $(e.item);
                let currentNode = $(e.target);
                switch (button.attr('data-command')) {
                    case 'edit':
                        let name = currentNode.find('.item').attr('data-name');
                        let url = currentNode.find('.item').attr('data-url');
                        let icon = currentNode.find('.item').attr('data-icon');
                        let target = currentNode.find('.item').attr('data-target');
                        
                        let modal = $('#' + modalMenuId);
                        $('[name="name"]', modal).val(name);
                        $('[name="url"]', modal).val(url);
                        $('[name="icon"]', modal).val(icon);
                        $('[name="target"]', modal).val(target);
                        treeview.select(currentNode);

                        if (modalMenu != null) {
                            modalMenu.show();
                        }
                        break;
                    case 'delete':
                        if (confirm('Bạn có chắc muốn xóa?')) {
                            treeview.remove(currentNode);
                            refreshTreeviewData();
                        }
                        break;
                    default:
                        break;
                }
            }
        });
    }

    bindTreeviewMenu(treeview);
    
    let list = getTreeviewList(treeview.dataSource.data(), null);
    if (list.length == 0) {
        $('#treeview').addClass('empty');
    } else {
        $('#treeview').removeClass('empty');
    }
    
    let frmCreateMenu = $('.frm-create-menu');
    $('.btn-save', frmCreateMenu).click(function (e) {
        let txtName = $('.txt-name', frmCreateMenu);
        if (txtName.val() == '') {
            txtName.focus();
            alert('Bạn phải nhập Tên!');
            return;
        }
        let txtUrl = $('.txt-url', frmCreateMenu);
        if (txtUrl.val() == '') {
            txtUrl.focus();
            alert('Bạn phải nhập đường dẫn!');
            return;
        }
    
        let txtIcon = $('.txt-icon', frmCreateMenu);
        let lsbTarget = $('.lsb-target', frmCreateMenu);
    
        let node = {
            id: Utilities.prototype.guid(),
            name: txtName.val(),
            url: txtUrl.val(),
            icon: txtIcon.val(),
            target: lsbTarget.val()
        };
        if (addTreeviewNode(treeview, null, node)) {
            refreshTreeviewData();
            txtName.val('');
            txtUrl.val('');
            lsbTarget.val('_self');
            txtIcon.val('');
        }
    });
    
    $('.btn-save', $('#' + modalMenuId)).click(function (e) {
        let modal = $('#' + modalMenuId);
        let txtName = $('[name="name"]', modal);
        if (txtName.val() == '') {
            txtName.focus();
            alert('Bạn phải nhập Tên!');
            return;
        }
        let txtUrl = $('[name="url"]', modal);
        let txtIcon = $('[name="icon"]', modal);
        let lsbTarget = $('[name="target"]', modal);
    
        let item = $(treeview.select()).find('.item');
        let node = {
            id: item.attr('data-id'),
            name: item.attr('data-name'),
            url: item.attr('data-url'),
            icon: item.attr('data-icon'),
            target: item.attr('data-target')
        };
        node.name = txtName.val();
        node.url = txtUrl.val();
        node.icon = txtIcon.val();
        node.target = lsbTarget.val();
        let selectedNode = treeview.dataItem(treeview.select());
        selectedNode.set('text', bindTreeviewNodeText(node));
        selectedNode.set('name', node.name);
        refreshTreeviewData();
    
        if (modalMenu != null) {
            modalMenu.hide();
        }
    });
});