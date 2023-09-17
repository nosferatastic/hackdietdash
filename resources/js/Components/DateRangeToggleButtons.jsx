
import ToggleButton from '@mui/material/ToggleButton';
import ToggleButtonGroup from '@mui/material/ToggleButtonGroup';
import React, { Component } from 'react';

class DateRangeToggleButtons extends Component {

    //Object storing key/value pairs corresponding to day intervals/titles for date range toggle buttons
    dateRangeIntervals = {'': "Show All", 7: 'Past Week', 14: 'Two Weeks', 28: 'Four Weeks', 90: '90 Days', 180: '180 Days', 365: 'Past Year'};

    constructor(props) {
        super(props);
        this.state = {active: props.active, function: props.function };
    }

    handleClick = (interval) => {
        if (typeof this.state.function === 'function') {
            this.state.function(interval);
        }
        this.setState ({
            active: interval
        });
    }

    componentDidUpdate(prevProps) {
        if(prevProps.active !== this.props.active){
            this.setState({          
                active: this.props.active
            });
        }
    }

    render() {
        return (

        <ToggleButtonGroup exclusive value={this.state.active.toString()}>
            {Object.entries(this.dateRangeIntervals).map((text) => {  
                        return (
                            <ToggleButton
                                onClick={() => this.handleClick(text[0])}
                                interval={text[0]}
                                value={text[0]}
                                key={text[0]}
                            >
                                {text[1]}
                            </ToggleButton>
                        );
                    }
                )
            }
            
        </ToggleButtonGroup>
        );
    }
}

export default DateRangeToggleButtons;
