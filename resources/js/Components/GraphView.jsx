

import { ComposedChart, Scatter, Line, CartesianGrid, XAxis, YAxis, ResponsiveContainer, ReferenceLine, Tooltip, Legend } from 'recharts';
import { Loader, Dimmer } from 'semantic-ui-react';
import DateRangeToggleButtons from '@/Components/DateRangeToggleButtons';
import React from 'react';
import { Paper } from '@mui/material';

function CustomTooltip({ active, payload, label }) {
  if (active && payload && payload.length && payload[0]) {
    var datetime = new Date(payload[0].payload.datetime);
    var weightlog = payload[0].payload;
    return (
      <Paper elevation={8} style={{padding:"10px", opacity:"85%"}}>
      <div className="custom-tooltip">
        <p className="label">{`Date: ${datetime.toDateString()}`}</p>
        <p className="intro">Weight (lbs): <i>{weightlog.weightlbs}</i></p>
        <p className="intro">Avg: <i>{weightlog.weightma}</i></p>
      </div>
      </Paper>
    );
  }

  return null;
};

class GraphView extends React.Component {

    constructor(props) {
      super(props);
    }

    render() {
        const { graphData, dateFilter, loading, error, handleClick } = this.props;
        if(error) {
            //Empty graphData
            return (<i>{"There was a problem loading the requested data."}</i>);
        } else if(loading) {
            return (
                <Dimmer active inverted>
                  <Loader />
                </Dimmer>
                );
        } else if(graphData.length == 0) {
            return (<i>{"You don't have any weight data stored."}</i>);
        } else {
            return (
              <React.Fragment>
                <ResponsiveContainer width={'99%'} aspect={2}>  
                <ComposedChart data={graphData}
                margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
                <CartesianGrid strokeDasharray="1 1" />
                <XAxis dataKey="datetime" tickFormatter={function(val) { return new Date(val).toDateString();}} />
                <YAxis domain={['dataMin - 2','dataMax + 2']} />
                <Tooltip  content={<CustomTooltip />} />
                <Legend />
                {dateFilter != "" && dateFilter <90 ?
                <Scatter isAnimationActive={false} type="monotone" dataKey="weightlbs" stroke="#000000" fillOpacity={0.25} strokeOpacity={0.25} strokeWidth={0.1} name="Weight Measurements" />
                : 
                ""
                }
                <Line type="monotone" dataKey="weightma" stroke="#82ca9d" dot={false} name="EMWA (Hacker's Diet Model)" />
                {graphData.map((answer) => {   
                        // Return the element. Also pass key     
                        if(answer.weightlbs) {
                        return (
                            <ReferenceLine key={answer.datetime} segment={[{ x: answer.datetime, y: answer.weightlbs }, { x: answer.datetime, y: answer.weightma }]} />
                            );
                        }
                        })}
                </ComposedChart>
                </ResponsiveContainer>
                <DateRangeToggleButtons function={handleClick} active={dateFilter} />  
                </React.Fragment>          
            );
        }
    
    }
}

export default GraphView;
