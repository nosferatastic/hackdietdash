

import { ComposedChart, Scatter, Line, CartesianGrid, XAxis, YAxis, ResponsiveContainer, ReferenceLine, Tooltip, Legend } from 'recharts';
import { Loader, Dimmer } from 'semantic-ui-react';
import DateRangeToggleButtons from '@/Components/DateRangeToggleButtons';
import React from 'react';

class GraphView extends React.Component {

    constructor(props) {
      super(props);
      this.state = { allData: [], graphData: [], loading: true, error: null, dateFilter: '' };
    }

    componentDidMount() {
        fetch(route('weightdash.query'), {
            method: "GET",
            cache: "no-cache", 
            credentials: "same-origin",
            headers: {
              "Content-Type": "application/json", // request content type
              "Accept": "application/json"
            },
            redirect: "follow",
            referrerPolicy: "no-referrer",
            // body: JSON.stringify(data) // Attach body with the request
          }).then(res => res.json())
          .then(
            (result) => {
              this.setState({
                loading: false,
                graphData: result,
                allData: result
              });
            },
            (error) => {
              this.setState({
                loading: false,
                error
              });
            }
          );
    }

    handleClick = (interval) => {
      var workingData = this.state.allData;
      var currentDate = new Date();
      if(interval == '') {
        this.setState({
          graphData: this.state.allData
        });
        return true;
      }
      var fwaDate = new Date().setDate(currentDate.getDate() - interval);
      workingData = workingData.filter(
        (data) => {
          var dataDate = new Date(data.datetime);
          if(dataDate > fwaDate) { return true; } else { return false; }
        }
      );
      this.setState({
        graphData: workingData
      })
    }

    render() {
        const { graphData, loading, error } = this.state;
        if(error) {
            //Empty graphData
            return (<i>{"err"}</i>);
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
                <Tooltip active={true} />
                <Legend />
                <Scatter isAnimationActive={false} type="monotone" dataKey="weightlbs" stroke="#000000" fillOpacity={0.25} strokeOpacity={0.25} strokeWidth={0.1} name="Weight Measurements" />
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
                <DateRangeToggleButtons function={this.handleClick} active = {this.state.dateFilter} />  
                </React.Fragment>          
            );
        }
    
    }
}

export default GraphView;
