import hljs from "highlight.js";
import 'highlight.js/styles/github.css';
import ClipboardJS from "clipboard";

document.addEventListener("DOMContentLoaded", function() {
    let codeBlocks = document.querySelectorAll("code");
    codeBlocks.forEach((codeBlock) => {
        // add the plain text as an attribute to the code block
        codeBlock.setAttribute("data-clipboard-text", codeBlock.textContent);
        hljs.highlightBlock(codeBlock);
    });

    addCopyToClipboardButton();
});

function addCopyToClipboardButton() {
    let codeBlocks = document.querySelectorAll("code");
    codeBlocks.forEach((codeBlock) => {
        let copyButton = document.createElement("button");
        copyButton.innerHTML = "Copy";
        copyButton.classList.add("copy-button");

        let copy = new ClipboardJS(copyButton, {
            text: function(trigger) {
                return trigger.parentElement.getAttribute("data-clipboard-text");
            },
        });

        copy.on("success", function(e) {
            copyButton.innerHTML = "Copied!";
                setTimeout(() => {
                copyButton.innerHTML = "Copy";
            }, 1000);

            e.clearSelection();
        });

        codeBlock.appendChild(copyButton);
    });
}
