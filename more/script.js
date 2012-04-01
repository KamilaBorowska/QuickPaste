(function () {
    "use strict"
    var html = document.getElementsByTagName('html')[0],
        body = document.getElementsByTagName('div')[0],
        code = document.getElementsByTagName('textarea')[0],
        h1 = document.getElementsByTagName('h1')[0],
        cancel = function (event) {
            if (event.preventDefault) event.preventDefault()
            event.dataTransfer.dropEffect = 'copy'
            return false
        },
        clip = new ZeroClipboard.Client,
        div = document.createElement('div')

    // Opera has weird issues with this script...
    if (!window.opera) {
        ZeroClipboard.setMoviePath('/more/ZeroClipboard.swf')
        div.innerHTML = '<div id="p1"><div id="p2">Copy link to ' +
                        'clipboard</div></div>'
        body.insertBefore(div, body.firstChild)
        clip.setText("" + document.location)
        clip.glue('p2', 'p1')
        clip.addEventListener('onMouseDown', function () {
            document.getElementById('p2').style.color = 'red'
        })
        clip.addEventListener('onMouseUp', function () {
            document.getElementById('p2').style.color = 'purple'
        })
    }

    if ('draggable' in html) {
        code.placeholder = 'Paste code or drag and drop file. ' +
                           "You don't need to choose language."

        html.addEventListener('dragenter', cancel)
        html.addEventListener('dragover', cancel)
        html.addEventListener('dragenter', cancel)
        html.addEventListener('drop', function (e) {
            var files = e.target.files || e.dataTransfer.files,
                reader = new FileReader
            if (files.length == 0) {
                // Try something else in case of no files.
                code.value = e.dataTransfer.getData('text/plain')
            }
            else if (files.length > 1) {
                alert('Sorry, but you can only paste one file at once.')
            }
            else {
                reader.onloadend = function () {
                    code.value = this.result
                }
                reader.readAsText(files[0])
            }
            return cancel(e)
        }, false)
    }
    hljs.lineNodes = true
    hljs.initHighlighting()
}())