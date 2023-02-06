/* Scrollmap: a scrollable map
 *
 * Code by yannsnow
 * */

define([
        "dojo", "dojo/_base/declare"
    ],
    function(dojo, declare) {
        return declare("ebg.scrollmapWithZoom", null, {
            constructor: function() {
                this.container_div = null;
                this.scrollable_div = null;
                this.surface_div = null;
                this.onsurface_div = null;
                this.clipped_div = null;
                this.animation_div = null;
                this.page = null;
                this.board_x = 0;
                this.board_y = 0;
                this.zoom = 1;
                this.maxZoom = 2;
                this.minZoom = 0.1;
                this.defaultZoom = null;
                this.defaultPosition = {
                    x: 0,
                    y: 0
                };
                this._prevZoom = 1;
                this.bEnableScrolling = true;
                this.zoomPinchDelta = 0.005;
                this.zoomWheelDelta = 0.001;
                this.bEnablePinchZooming = false;
                this.bEnableWheelZooming = false;
                this.zoomChangeHandler = null;
                this.bScrollDeltaAlignWithZoom = true;
                this.scrollDelta = 0;
                this._scrollDeltaAlignWithZoom = 0;
                this._pointers = [];
                this._classNameSuffix = '';
                this.resizeObserver = new ResizeObserver(this.onResize.bind(this));
            },

            create: function(container_div, scrollable_div, surface_div, onsurface_div, clipped_div = null, animation_div = null, page = null, create_extra = null) {
                console.log("ebg.scrollmapWithZoom create");
                this.page = page;
                this.container_div = container_div;
                this.scrollable_div = scrollable_div;
                this.surface_div = surface_div;
                this.onsurface_div = onsurface_div;
                this.clipped_div = clipped_div;
                this.animation_div = animation_div;

                var styleElt = document.createElement("style");
                const styleSheetContent = String.raw`
                    #${container_div.id}.scrollmap-zoomed{
                        transform:;
                    }
                `;
                // styleElt.type = "text/css";
                styleElt.id = 'css-' + container_div.id;
                styleElt.appendChild(document.createTextNode(styleSheetContent));
                document.head.appendChild(styleElt);

                if (create_extra !== null)
                    create_extra(this);

                dojo.connect(this.surface_div, 'onpointerdown', this, 'onPointerDown');
                dojo.connect(this.container_div, 'onwheel', this, 'onWheel');

                if (this.defaultZoom === null)
                    this.defaultZoom = this.zoom;
                this.setMapZoom(this.defaultZoom);
                this.scrollto(0, 0);
                this.resizeObserver.observe(this.container_div);
            },

            createCompletely: function(container_div, page = null, create_extra = null) {
                console.log("createCompletely");
                var tmpl = String.raw`
                    <div class="scrollmap_overflow_clipped">
                        <div class="scrollmap_scrollable"></div>
                        <div class="scrollmap_surface" ></div>
                        <div class="scrollmap_onsurface"></div>
                    </div>
                    <i class="scrollmap_movetop fa fa-chevron-up scrollmap_centericon"></i>
                    <i class="scrollmap_moveleft fa fa-chevron-left scrollmap_centericon"></i>
                    <i class="scrollmap_moveright fa fa-chevron-right scrollmap_centericon"></i>
                    <i class="scrollmap_movedown fa fa-chevron-down scrollmap_centericon"></i>
                    <i class="scrollmap_zoomplus fa fa-search-plus scrollmap_centericon"></i>
                    <i class="scrollmap_zoomminus fa fa-search-minus scrollmap_centericon"></i>
                    <i class="scrollmap_reset fa fa-refresh scrollmap_centericon"></i>
                    <div class="scrollmap_anim"></div>
                `;
                this._classNameSuffix = 'scrollmap_';
                dojo.place(tmpl, container_div);
                var scrollable_div = container_div.querySelector('.scrollmap_scrollable');
                var surface_div = container_div.querySelector('.scrollmap_surface');
                var onsurface_div = container_div.querySelector('.scrollmap_onsurface');
                var clipped_div = container_div.querySelector('.scrollmap_overflow_clipped');
                var animation_div = container_div.querySelector('.scrollmap_anim');
                //container_div.innerHTML = tmpl;
                //this.create(container_div, scrollable_div, surface_div, onsurface_div, clipped_div, animation_div, page);
                this.create(container_div, scrollable_div, surface_div, onsurface_div, clipped_div, animation_div, page, create_extra);
            },

            _init: function() {},

            onResize: function() {
                // console.log("onResize");
                this.scrollto(this.board_x, this.board_y, 0, 0);
            },

            _findPointerIndex: function(event) {
                var i = this._pointers.length;
                while (i--) {
                    if (this._pointers[i].pointerId === event.pointerId) {
                        return i;
                    }
                }
                return -1;
            },

            _addPointer: function(event) {
                const i = this._findPointerIndex(event);
                // Update if already present
                if (i > -1) {
                    const prevEv = this._pointers[i];
                    this._pointers.splice(i, 1, event);
                    return prevEv;
                } else
                    this._pointers.push(event);
            },

            _removePointer: function(event) {
                const i = this._findPointerIndex(event);
                if (i > -1) {
                    this._pointers.splice(i, 1);
                }
            },

            _getPointerPrevEvent: function(event) {
                const i = this._findPointerIndex(event);
                if (i > -1) {
                    return this._pointers[i];
                }
            },

            _getPageZoom: function() {
                var pageZoom = 1;
                if (this.page === null) {
                    var pageZoomStr = $("page-content").style.getPropertyValue("zoom");
                    pageZoom = 1;
                    if (pageZoomStr !== "")
                        pageZoom = parseFloat($("page-content").style.getPropertyValue("zoom"));
                } else
                    pageZoom = this.page.gameinterface_zoomFactor;
                return pageZoom;
            },

            _getXYCoord: function(ev, ev2) {
                const width = dojo.style(this.container_div, "width");
                const height = dojo.style(this.container_div, "height");
                const containerRect = this.container_div.getBoundingClientRect();
                var clientX = ev.clientX;
                var clientY = ev.clientY;
                if (typeof ev2 !== 'undefined') {
                    clientX = (clientX + ev2.clientX) / 2;
                    clientY = (clientY + ev2.clientY) / 2;
                }

                var pageZoom = this._getPageZoom();
                const x = clientX / pageZoom - containerRect.x - width / 2;
                const y = clientY / pageZoom - containerRect.y - height / 2;
                return [x, y];
            },

            onPointerDown: function(ev) {
                if (!this.bEnableScrolling && !this.bEnablePinchZooming)
                    return;
                if (this._pointers.length == 0) {
                    this.onpointermove_handler = dojo.connect(document, "onpointermove", this, "onPointerMove");
                    this.onpointerup_handler = dojo.connect(document, "onpointerup", this, "onPointerUp");
                    this.onpointercancel_handler = dojo.connect(document, "onpointercancel", this, "onPointerUp");
                }
                this._addPointer(ev);
            },

            onPointerMove: function(ev) {
                if ((!this.bEnableScrolling && !this.bEnablePinchZooming))
                    return;
                ev.preventDefault();
                const prevEv = this._addPointer(ev);

                // If one pointer is move, drag the map
                if (this._pointers.length === 1) {
                    if (!this.bEnableScrolling)
                        return;
                    if ((typeof prevEv !== 'undefined')) {
                        const [x, y] = this._getXYCoord(ev);
                        const [xPrev, yPrev] = this._getXYCoord(prevEv);
                        this.scroll(x - xPrev, y - yPrev, 0, 0);
                    }
                }
                // If two _pointers are move, check for pinch gestures
                else if (this._pointers.length === 2) {
                    if (!this.bEnablePinchZooming)
                        return;

                    // Calculate the distance between the two _pointers
                    const ev1 = this._pointers[0];
                    const ev2 = this._pointers[1];
                    const curDist = Math.sqrt(
                        Math.pow(Math.abs(ev2.clientX - ev1.clientX), 2) +
                        Math.pow(Math.abs(ev2.clientY - ev1.clientY), 2)
                    );
                    const [x, y] = this._getXYCoord(ev1, ev2);
                    if (this._prevDist > 0.0) {
                        // const diff = curDist - this._prevDist;
                        // newZoom = this.zoom * (1 + this.zoomPinchDelta * diff);
                        const newZoom = this.zoom * (curDist / this._prevDist);
                        this.setMapZoom(newZoom, x, y);
                        this.scroll(x - this._xPrev, y - this._yPrev, 0, 0);
                    }

                    // Cache the distance for the next move event
                    this._prevDist = curDist;
                    this._xPrev = x;
                    this._yPrev = y;
                }
                dojo.stopEvent(ev);
            },

            onPointerUp: function(ev) {
                this._removePointer(ev);
                // If no pointer left, stop drag or zoom the map
                if (this._pointers.length === 0) {
                    dojo.disconnect(this.onpointermove_handler);
                    dojo.disconnect(this.onpointerup_handler);
                    dojo.disconnect(this.onpointercancel_handler);
                }

                // If the number of _pointers down is less than two then reset diff tracker
                if (this._pointers.length < 2) {
                    this._prevDist = -1;
                }
            },

            onWheel: function(evt) {
                if ((!this.bEnableWheelZooming) || (evt.ctrlKey))
                    return;
                evt.preventDefault();
                const [x, y] = this._getXYCoord(evt);
                this.changeMapZoom(evt.deltaY * -this.zoomWheelDelta, x, y);
            },

            scroll: function(dx, dy, duration, delay) {
                if (typeof duration == 'undefined') {
                    duration = 350; // Default duration
                }
                if (typeof delay == 'undefined') {
                    delay = 0; // Default delay
                }
                //console.log(dx+' '+dy);
                this.scrollto(this.board_x + dx, this.board_y + dy, duration, delay);
            },

            // Scroll the board to make it centered on given position
            scrollto: function(x, y, duration, delay) {
                if (typeof duration == 'undefined') {
                    duration = 350; // Default duration
                }
                if (typeof delay == 'undefined') {
                    delay = 0; // Default delay
                }

                const width = dojo.style(this.container_div, "width");
                const height = dojo.style(this.container_div, "height");

                const board_x = toint(x + width / 2);
                const board_y = toint(y + height / 2);

                this.board_x = x;
                this.board_y = y;

                if ((duration == 0) && (delay == 0)) {
                    if (this.animation_div !== null) {
                        dojo.style(this.animation_div, "left", board_x + "px");
                        dojo.style(this.animation_div, "top", board_y + "px");
                    }
                    dojo.style(this.scrollable_div, "left", board_x + "px");
                    dojo.style(this.onsurface_div, "left", board_x + "px");
                    dojo.style(this.scrollable_div, "top", board_y + "px");
                    dojo.style(this.onsurface_div, "top", board_y + "px");
                    // dojo.style( dojo.body(), "backgroundPosition", board_x+"px "+board_y+"px" );
                    return;
                }
                var anim1 = dojo.fx.slideTo({
                    node: this.scrollable_div,
                    top: board_y,
                    left: board_x,
                    unit: "px",
                    duration: duration,
                    delay: delay
                });
                var anim2 = dojo.fx.slideTo({
                    node: this.onsurface_div,
                    top: board_y,
                    left: board_x,
                    unit: "px",
                    duration: duration,
                    delay: delay
                });
                var anims = null;
                if (this.animation_div !== null) {
                    var anim3 = dojo.fx.slideTo({
                        node: this.animation_div,
                        top: board_y,
                        left: board_x,
                        unit: "px",
                        duration: duration,
                        delay: delay
                    });
                    anims = [anim1, anim2, anim3];
                } else
                    anims = [anim1, anim2];
                var anim = dojo.fx.combine(anims);
                anim.play();
            },

            // Scroll map in order to center everything
            // By default, take all elements in movable_scrollmap
            //  you can also specify (optional) a custom CSS query to get all concerned DOM elements
            scrollToCenter: function(custom_css_query) {
                const center = this.getMapCenter(custom_css_query);
                this.scrollto(-center.x, -center.y);
                return {
                    x: -center.x,
                    y: -center.y
                };
            },

            getMapCenter: function(custom_css_query) {
                // Get all elements inside and get their max x/y/w/h
                var max_x = 0;
                var max_y = 0;
                var min_x = 0;
                var min_y = 0;

                var css_query = "> *";
                var css_query_div = this.scrollable_div;
                if (typeof custom_css_query != 'undefined') {
                    css_query = custom_css_query;
                    css_query_div = null;
                }

                dojo.query(css_query, css_query_div).forEach(dojo.hitch(this, function(node) {
                    max_x = Math.max(max_x, dojo.style(node, 'left') + dojo.style(node, 'width'));
                    min_x = Math.min(min_x, dojo.style(node, 'left'));

                    max_y = Math.max(max_y, dojo.style(node, 'top') + dojo.style(node, 'height'));
                    min_y = Math.min(min_y, dojo.style(node, 'top'));

                    //                alert( node.id );
                    //                alert( min_x+','+min_y+' => '+max_x+','+max_y );
                }));

                return {
                    x: (min_x + max_x) / 2,
                    y: (min_y + max_y) / 2
                };
            },

            changeMapZoom: function(diff, x = 0, y = 0) {
                const newZoom = this.zoom + diff;
                this.setMapZoom(newZoom, x, y);
            },

            setMapZoom: function(zoom, x = 0, y = 0) {
                this.zoom = Math.min(Math.max(zoom, this.minZoom), this.maxZoom);

                if (this.bScrollDeltaAlignWithZoom)
                    this._scrollDeltaAlignWithZoom = this.scrollDelta * zoom;
                else
                    this._scrollDeltaAlignWithZoom = this.scrollDelta;
                this.setScale(this.scrollable_div, this.zoom);
                this.setScale(this.onsurface_div, this.zoom);
                if (this.animation_div !== null)
                    this.setScale(this.animation_div, this.zoom);
                const css = String.raw;
                const styleSheetContent = css`
                    #${this.container_div.id} .scrollmap-zoomed{
                        transform:scale(${this.zoom});
                    }
                `;
                document.querySelector('#css-' + this.container_div.id).textContent = styleSheetContent;
                if (this.zoomChangeHandler);
                this.zoomChangeHandler(this.zoom);
                const zoomDelta = this.zoom / this._prevZoom;
                //console.log(x+' '+ y+' '+ zoomDelta+' '+ this.zoom);
                this.scrollto((this.board_x * zoomDelta) + x * (1 - zoomDelta), (this.board_y * zoomDelta) + y * (1 - zoomDelta), 0, 0);
                this._prevZoom = this.zoom;
            },

            setScale: function(elemId, scale) {
                dojo.style($(elemId), 'transform', 'scale(' + scale + ')');
            },

            //////////////////////////////////////////////////
            //// Scroll with buttons

            // Optional: setup on screen arrows to scroll the board
            setupOnScreenArrows: function(scrollDelta, bScrollDeltaAlignWithZoom = true) {
                this.scrollDelta = scrollDelta;
                this.bScrollDeltaAlignWithZoom = bScrollDeltaAlignWithZoom;
                if (this.bScrollDeltaAlignWithZoom)
                    this._scrollDeltaAlignWithZoom = scrollDelta * this.zoom;
                else
                    this._scrollDeltaAlignWithZoom = scrollDelta;

                // Old controls - for compatibility
                if ($('movetop')) {
                    dojo.connect($('movetop'), 'onclick', this, 'onMoveTop');
                }
                if ($('moveleft')) {
                    dojo.connect($('moveleft'), 'onclick', this, 'onMoveLeft');
                }
                if ($('moveright')) {
                    dojo.connect($('moveright'), 'onclick', this, 'onMoveRight');
                }
                if ($('movedown')) {
                    dojo.connect($('movedown'), 'onclick', this, 'onMoveDown');
                }

                // New controls
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'movetop').connect('onclick', this, 'onMoveTop').style('cursor', 'pointer').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'movedown').connect('onclick', this, 'onMoveDown').style('cursor', 'pointer').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'moveleft').connect('onclick', this, 'onMoveLeft').style('cursor', 'pointer').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'moveright').connect('onclick', this, 'onMoveRight').style('cursor', 'pointer').style('display', 'block');

                this.showOnScreenArrows();
            },

            showOnScreenArrows: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'movetop').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'moveleft').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'moveright').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'movedown').style('display', 'block');
            },

            hideOnScreenArrows: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'movetop').style('display', 'none');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'moveleft').style('display', 'none');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'moveright').style('display', 'none');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'movedown').style('display', 'none');
            },

            onMoveTop: function(evt) {
                // console.log("onMoveTop");
                evt.preventDefault();
                this.scroll(0, this._scrollDeltaAlignWithZoom);
            },

            onMoveLeft: function(evt) {
                // console.log("onMoveLeft");
                evt.preventDefault();
                this.scroll(this._scrollDeltaAlignWithZoom, 0);
            },

            onMoveRight: function(evt) {
                // console.log("onMoveRight");
                evt.preventDefault();
                this.scroll(-this._scrollDeltaAlignWithZoom, 0);
            },

            onMoveDown: function(evt) {
                // console.log("onMoveDown");
                evt.preventDefault();
                this.scroll(0, -this._scrollDeltaAlignWithZoom);
            },

            isVisible: function(x, y) {
                const width = dojo.style(this.container_div, "width");
                const height = dojo.style(this.container_div, "height");

                if (x >= (-this.board_x - width / 2) && x <= (-this.board_x + width / 2)) {
                    if (y >= (-this.board_y - height / 2) && y < (-this.board_y + height / 2)) {
                        return true;
                    }
                }

                return false;
            },

            ///////////////////////////////////////////////////
            ///// Enable / disable scrolling
            enableScrolling: function() {
                if (!this.bEnableScrolling) {
                    this.bEnableScrolling = true;
                }
                this.showOnScreenArrows();
            },

            disableScrolling: function() {
                if (this.bEnableScrolling) {
                    this.bEnableScrolling = false;
                }
                // hide arrows
                this.hideOnScreenArrows();
            },

            //////////////////////////////////////////////////
            //// Zoom with buttons
            setupOnScreenZoomButtons: function(zoomDelta) {
                this.zoomDelta = zoomDelta;

                // Old controls - for compatibility
                if ($('zoomplus')) {
                    dojo.connect($('zoomplus'), 'onclick', this, 'onZoomIn');
                }
                if ($('zoomminus')) {
                    dojo.connect($('zoomminus'), 'onclick', this, 'onZoomOut');
                }

                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'zoomplus').connect('onclick', this, 'onZoomIn').style('cursor', 'pointer');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'zoomminus').connect('onclick', this, 'onZoomOut').style('cursor', 'pointer');

                this.showOnScreenZoomButtons();

            },

            showOnScreenZoomButtons: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'zoomplus').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'zoomminus').style('display', 'block');
            },

            hideOnScreenZoomButtons: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'zoomplus').style('display', 'none');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'zoomminus').style('display', 'none');
            },

            onZoomIn: function(evt) {
                evt.preventDefault();
                this.changeMapZoom(this.zoomDelta);
            },

            onZoomOut: function(evt) {
                evt.preventDefault();
                this.changeMapZoom(-this.zoomDelta);
            },

            //////////////////////////////////////////////////
            //// Reset with buttons
            setupOnScreenResetButtons: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'reset').connect('onclick', this, 'onReset').style('cursor', 'pointer');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'back_to_center').connect('onclick', this, 'onBackToCenter').style('cursor', 'pointer');
                this.showOnScreenResetButtons();
            },

            showOnScreenResetButtons: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'reset').style('display', 'block');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'back_to_center').style('display', 'block');
            },

            hideOnScreenResetButtons: function() {
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'reset').style('display', 'none');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'back_to_center').style('display', 'block');
            },

            onReset: function(evt) {
                this.setMapZoom(this.defaultZoom);
                this.scrollto(this.defaultPosition.x, this.defaultPosition.y);
            },

            onBackToCenter: function(evt) {
                this.scrollto(this.defaultPosition.x, this.defaultPosition.y);
            },

            //////////////////////////////////////////////////
            //// Increase/decrease display height with buttons
            setupEnlargeReduceButtons: function(incrHeightDelta, incrHeightKeepInPos, minHeight) {
                // Old controls - for compatibility
                if ($('enlargedisplay')) {
                    dojo.connect($('enlargedisplay'), 'onclick', this, 'onIncreaseDisplayHeight');
                }
                if ($('reducedisplay')) {
                    dojo.connect($('reducedisplay'), 'onclick', this, 'onDecreaseDisplayHeight');
                }

                this.incrHeightDelta = incrHeightDelta;
                this.incrHeightKeepInPos = incrHeightKeepInPos;
                this.minHeight = minHeight;
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'enlargedisplay').connect('onclick', this, 'onIncreaseDisplayHeight').style('cursor', 'pointer');
                dojo.query('#' + this.container_div.id + ' .' + this._classNameSuffix + 'reducedisplay').connect('onclick', this, 'onDecreaseDisplayHeight').style('cursor', 'pointer');
            },

            onIncreaseDisplayHeight: function(evt) {
                evt.preventDefault();

                var current_height = toint(dojo.style($('map_container'), 'height'));
                if (this.incrHeightKeepInPos)
                    this.board_y -= this.incrHeightDelta / 2;
                dojo.style($('map_container'), 'height', (current_height + this.incrHeightDelta) + 'px');
            },

            onDecreaseDisplayHeight: function(evt) {
                evt.preventDefault();

                var current_height = toint(dojo.style($('map_container'), 'height'));
                var new_height = Math.max((current_height - this.incrHeightDelta), this.minHeight);
                if (this.incrHeightKeepInPos)
                    this.board_y += (current_height - new_height) / 2;
                dojo.style($('map_container'), 'height', new_height + 'px');
            },
        });
    });