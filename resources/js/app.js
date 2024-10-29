import Alpine from 'alpinejs'
import intersect from '@alpinejs/intersect'

// plugins have to be imported before Alpine is started
Alpine.plugin(intersect)

window.Alpine = Alpine
Alpine.start()


document.addEventListener('DOMContentLoaded', function () {
    assignTabSliderEvents();
});


function assignTabSliderEvents() {
    // do that for each .tab-slider
    let tabSliders = document.querySelectorAll(".tab-slider")

    tabSliders.forEach(tabSlider => {
        let tabs = tabSlider.querySelectorAll(".tab")
        let panels = tabSlider.querySelectorAll(".tab-panel")

        tabs.forEach(tab => {
            tab.addEventListener("click", ()=>{
                let tabTarget = tab.getAttribute("aria-controls")
                // set all tabs as not active
                tabs.forEach(tab =>{
                    tab.setAttribute("data-active-tab", "false")
                    tab.setAttribute("aria-selected", "false")
                })

                // set the clicked tab as active
                tab.setAttribute("data-active-tab", "true")
                tab.setAttribute("aria-selected", "true")

                panels.forEach(panel =>{
                    let panelId = panel.getAttribute("id")
                    if(tabTarget === panelId){
                        panel.classList.remove("hidden", "opacity-0")
                        panel.classList.add("block", "opacity-100")
                        // animate panel fade in

                        panel.animate([
                            { opacity: 0, maxHeight: 0 },
                            { opacity: 1, maxHeight: "100%" }
                        ], {
                            duration: 500,
                            easing: "ease-in-out",
                            fill: "forwards"
                        })

                    } else {
                        panel.classList.remove("block", "opacity-100")
                        panel.classList.add("hidden", "opacity-0")

                        // animate panel fade out
                        panel.animate([
                            { opacity: 1, maxHeight: "100%" },
                            { opacity: 0, maxHeight: 0 }
                        ], {
                            duration: 500,
                            easing: "ease-in-out",
                            fill: "forwards"
                        })
                    }
                })
            })
        })

        let activeTab = tabSlider.querySelector(".tab[data-active-tab='true']")
        activeTab.click()
    })

}
