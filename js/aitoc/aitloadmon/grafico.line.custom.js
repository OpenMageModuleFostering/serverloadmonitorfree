/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
Grafico.LineGraphCustom = Class.create(Grafico.BaseGraph, {
    chartDefaults: function () {
        return {
            line: true,
            start_at_zero: true,
            stroke_width: 5,
            curve_amount: 10
        };
    },

    setChartSpecificOptions: function () {
    },

    calculateStep: function () {
        return (this.graph_width - (this.options.plot_padding * 2)) / (this.data_size - 1);
    },

    startPlot: function (cursor, x, y, color) {
        cursor.moveTo(x, y);
        cursor.attr({'stroke-opacity': this.options.opacity});

    },

    drawPlot: function (index, cursor, x, y, color, coords, datalabel, element, graphindex) {

        if(typeof lineGraphs[graphindex+1] == 'undefined')lineGraphs[graphindex+1]=[];

        if (this.options.markers === 'circle') {
            this.drawGraphMarkers(index, x, y, color, datalabel, element);
        } else if (this.options.markers === 'value') {
            this.drawGraphValueMarkers(index, x, y, color, datalabel, element, graphindex);
        }

        if (index === 0) {
            lineGraphs[graphindex+1].push(cursor);
            return this.startPlot(cursor, x - 0.5, y, color);
        }
        if (this.options.curve_amount) {
            ww = cursor.cplineTo(x, y, this.options.curve_amount);
        } else {
            ww = cursor.lineTo(x, y);

        }
    },

    drawGraphMarkers: function (index, x, y, color, datalabel, element) {
        var circle = this.paper.circle(x, y, this.options.marker_size),
            old_marker_size = this.options.marker_size,
            color2 = this.options.hover_color || color,
            new_marker_size = parseInt(1.7 * old_marker_size, 10);

        circle.attr({ 'stroke-width': '1px', stroke: this.options.background_color, fill: color });
        this.globalMarkerSet.push(circle);

        circle.hover(function (event) {
            circle.animate({r: new_marker_size, fill: color2}, 200);
        }, function (event) {
            circle.animate({r: old_marker_size, fill: color}, 200);
        });
    },

    drawGrid: function () {
        var path = this.paper.path().attr({ stroke: this.options.grid_color}),
            y, x, x_labels, i;

        if (this.options.show_horizontal_grid) {
            y = this.graph_height + this.y_padding_top;
            for (i = 0; i < this.y_label_count + 1; i++) {
                path.moveTo(this.x_padding_left - 0.5, parseInt(y, 10) + 0.5);
                path.lineTo(this.x_padding_left + this.graph_width - 0.5, parseInt(y, 10) + 0.5);
                y = y - (this.graph_height / this.y_label_count);
            }
        }
        if (this.options.show_vertical_grid) {
            x = this.x_padding_left + this.options.plot_padding + this.grid_start_offset;
            x_labels = this.options.labels.length;

            for (i = 0; i < x_labels; i++) {
                if ((this.options.hide_empty_label_grid === true && this.options.labels[i] !== "") || this.options.hide_empty_label_grid === false) {
                    path.moveTo(parseInt(x, 10), this.y_padding_top);
                    path.lineTo(parseInt(x, 10), this.y_padding_top + this.graph_height);
                }
                x = x + this.step;
            }
        }


        if (this.options.zones) {
            zones = this.options.zones;
            for (i = 0; i < zones.length; i++)
            {
                if(zones[i] > this.top_value)
                {
                    zones[i] = this.top_value;
                }
            }

            var zone_heights = [];
            zone_heights[0] = (this.top_value-zones[2])/this.top_value*this.graph_height;
            zone_heights[1] = (zones[2]-zones[1])/this.top_value*this.graph_height;
            zone_heights[2] = (zones[1]-zones[0])/this.top_value*this.graph_height;
            zone_heights[3] =  zones[0]/this.top_value*this.graph_height;

            this.paper.rect(this.x_padding_left,this.y_padding_top,this.graph_width, zone_heights[0]).attr({'fill': 'black', 'fill-opacity': '0.15', 'stroke-opacity':'0'});
            this.paper.rect(this.x_padding_left,this.y_padding_top+zone_heights[0],this.graph_width,zone_heights[1]).attr({'fill': 'red', 'fill-opacity': '0.1', 'stroke-opacity':'0'});
            this.paper.rect(this.x_padding_left,this.y_padding_top+zone_heights[0]+zone_heights[1],this.graph_width,zone_heights[2]).attr({'fill': 'yellow', 'fill-opacity': '0.15', 'stroke-opacity':'0'});
            this.paper.rect(this.x_padding_left,this.y_padding_top+zone_heights[0]+zone_heights[1]+zone_heights[2],this.graph_width,zone_heights[3]).attr({'fill': 'green', 'fill-opacity': '0.15', 'stroke-opacity':'0'});
        }
    },

    drawGraphValueMarkers: function (index, x, y, color, datalabel, element, graphindex) {
        index += this.options.odd_horizontal_offset>1 ? this.options.odd_horizontal_offset : 0;
        index -= this.options.stacked_fill || this.options.area ? 1 : 0;

        if(!this.options.label_sizes[graphindex+1][index])
        {
            tmpsize = 1;
        }
        else
        {
            tmpsize = this.options.label_sizes[graphindex+1][index];
        }

        var circle = this.paper.circle(x, y, Math.log(tmpsize));

        circle.attr({ 'stroke-width': '1px', stroke: this.options.background_color, fill: color, 'fill-opacity': this.options.opacity});
        this.globalMarkerSet.push(circle);

        lineGraphs[graphindex+1].push(circle);

        var currentset   = this.options.stacked ? this.real_data : this.data_sets,
            currentvalue = currentset.collect(function (data_set) { return data_set[1][index]; })[graphindex];

        if (currentvalue) {
            currentvalue = "" + currentvalue.toString().split('.');
            if (currentvalue[1]) {
                currentvalue[1] = currentvalue[1].truncate(3, '');
            }
        }
        if (
            (this.options.line||this.options.stacked) || //if the option is a line graph
                ((this.options.stacked_fill||this.options.area) && index != -1) && //if it's stacked or an area and it's not the first
                    typeof currentvalue != "undefined") { //if there is a current value

            var rectx  = x-(this.step/2),
                recty  = y-[this.options.stroke_width/2, this.options.hover_radius].max(),
                rectw  = this.step,
                recth  = [this.options.stroke_width, this.options.hover_radius*2].max(),
                circle = this.paper.circle(x, y, this.options.marker_size == 0 ? [this.options.stroke_width*1.5, this.step].min() : this.options.marker_size).attr({ 'stroke-width': '1px', stroke: this.options.background_color, fill: color,opacity:0}),
                block  = this.paper.rect(rectx, recty, rectw, recth).attr({fill:color, 'stroke-width': 0, stroke : color,opacity:0});

            if (this.options.datalabels) {
                if(typeof(datalabel) == 'function') {
                    datalabel = datalabel.call(this, index, currentvalue);
                } else {
                    datalabel = datalabel + ": " + currentvalue + " s\r\n" + "Page views: " + this.options.label_sizes[graphindex+1][index] + " / " + "Concurrent page views: " + this.options.concurrents[graphindex+1][index] + "\r\n" + this.options.dot_labels[index];
                }
            } else {
                datalabel = "" + currentvalue;
            }
            datalabel += this.options.vertical_label_unit ? " " + this.options.vertical_label_unit : "";

            var hoverSet = this.paper.set(),
                textpadding = 4,
                text = this.paper.text(circle.attrs.cx, circle.attrs.cy - (this.options.font_size * 1.5) -2 * textpadding, datalabel).attr({'font-size': this.options.font_size, fill:this.options.hover_text_color, opacity: 1}),
                textbox = text.getBBox(),
                roundRect= this.drawRoundRect(text, textbox, textpadding),
                nib = this.drawNib(text, textbox, textpadding);

            hoverSet.push(circle,roundRect,nib,text).attr({opacity:0});
            lineGraphs[graphindex+1].push(circle,roundRect,nib,text);
            this.checkHoverPos({rect:roundRect,set:hoverSet,marker:circle,nib:nib,textpadding:textpadding});
            this.globalHoverSet.push(hoverSet);
            this.globalBlockSet.push(block);

            block.hover(function (event) {
                hoverSet.animate({opacity:1},200);
            }, function (event) {
                hoverSet.animate({opacity:0},200);
            });
        }
    }
});
