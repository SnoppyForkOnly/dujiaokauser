<form action="{{('update-home')}}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="form-row align-items-center">
        <div class="col-auto">
            <label for="searchText" class="sr-only">查找内容:</label>
            <input type="text" class="form-control mb-2" id="searchText" name="searchText" placeholder="查找内容">
        </div>
        <div class="col-auto">
            <label for="replaceText" class="sr-only">替换为:</label>
            <input type="text" class="form-control mb-2" id="replaceText" name="replaceText" placeholder="替换为">
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-info mb-2" onclick="findTextInTextarea()">查找下一个</button>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-warning mb-2" onclick="replaceNext()">替换下一个</button>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-success mb-2" onclick="replaceAll()">替换全部</button>
        </div>
    </div>
    <textarea name="fileContent" rows="20" class="form-control">{{ $fileContent }}</textarea>
    <br>
    <button type="submit" class="btn btn-primary">保存更改</button>
</form>


<script>
let lastIndex = 0;
let lastSearchText = '';

function findTextInTextarea() {
    var textarea = document.querySelector('textarea[name="fileContent"]');
    var searchText = document.getElementById('searchText').value;

    if (!searchText) {
        alert('请输入查找内容');
        return;
    }

    if (searchText !== lastSearchText) {
        // 如果查找的文本变了，重置查找位置
        lastIndex = 0;
        lastSearchText = searchText;
    }

    var content = textarea.value;
    var indexOfMatch = content.indexOf(searchText, lastIndex);

    if (indexOfMatch === -1) {
        if (lastIndex > 0) {
            lastIndex = 0; // 如果之前有查找到，而现在没找到，说明已经是文档末尾了，重置lastIndex并重新查找
            findTextInTextarea(); // 重新查找
            return;
        }
        alert('未找到指定内容');
        return;
    }

    lastIndex = indexOfMatch + searchText.length; // 更新查找位置
    textarea.focus();
    textarea.setSelectionRange(indexOfMatch, lastIndex); // 将光标设置到匹配项之后

    // 滚动到匹配项的位置
    scrollToMatch(textarea, indexOfMatch);
}

function scrollToMatch(textarea, indexOfMatch) {
    // 近似计算匹配项的位置
    var matchLine = textarea.value.substring(0, indexOfMatch).split("\n").length - 1;
    var lineHeight = parseInt(window.getComputedStyle(textarea).lineHeight, 10);
    var scrollPosition = lineHeight * matchLine;
    
    // 滚动到计算出的位置
    textarea.scrollTop = scrollPosition - textarea.offsetHeight / 2; // 尝试将匹配项置于文本框中央
}

function replaceNext() {
    var textarea = document.querySelector('textarea[name="fileContent"]');
    var searchText = document.getElementById('searchText').value;
    var replaceText = document.getElementById('replaceText').value;

    if (!searchText) {
        alert('请输入查找内容');
        return;
    }

    var content = textarea.value;
    var indexOfMatch = content.indexOf(searchText, lastIndex - searchText.length);
    if (indexOfMatch !== -1) {
        var confirmed = confirm("确认替换当前匹配项吗？");
        if (confirmed) {
            textarea.value = content.substring(0, indexOfMatch) + replaceText + content.substring(indexOfMatch + searchText.length);
            lastIndex = indexOfMatch + replaceText.length; // 更新 last index 为替换文本之后
            textarea.focus();
            textarea.setSelectionRange(lastIndex, lastIndex);
        }
    } else {
        if (lastIndex > 0) { // 如果之前有替换到，而现在没找到，说明已经是文档末尾了，重置lastIndex
            lastIndex = 0;
        } else {
            alert('未找到指定内容');
        }
    }
}

function replaceAll() {
    var textarea = document.querySelector('textarea[name="fileContent"]');
    var searchText = document.getElementById('searchText').value;
    var replaceText = document.getElementById('replaceText').value;
    var content = textarea.value;
    var matches = content.match(new RegExp(searchText, 'g'));

    if (!matches) {
        alert('未找到指定内容');
        return;
    }

    var confirmed = confirm("确认替换所有匹配项吗？");
    if (confirmed) {
        textarea.value = content.replace(new RegExp(searchText, 'g'), replaceText);
        lastIndex = 0; // 替换全部后重置 lastIndex
        alert('替换了 ' + matches.length + ' 次');
    }
}
</script>
