import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import GraphView from '@/Components/GraphView';
import { Head } from '@inertiajs/react';
import { Button, Segment, Table, Menu, Icon, Grid, Statistic } from 'semantic-ui-react';
import { useEffect } from 'react';
import * as React from "react";


  var prevweight, diff = 0;
  function epoch (date) {
    return Date.parse(date)
  }

export default function WeightDashboard({ auth, weightData }) {

    const defaultRange= 28;
    var statistic = "";

    const [allData, setAllData] = React.useState([]);
    const [graphData, setGraphData] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const [dateFilter, setdateFilter] = React.useState(defaultRange);

    if(loading || graphData.length == 0) {
        statistic = "";
    } else if((graphData[graphData.length-1].weightma - graphData[0].weightma).toFixed(2) < 0) {
        statistic =  (<Statistic.Label style={{textTransform:"lowercase"}}>
            <Icon name="down arrow" /> 
            {(-1*(graphData[graphData.length-1].weightma - graphData[0].weightma)).toFixed(2)} lbs
        </Statistic.Label>  );
    } else {
        statistic =  (<Statistic.Label style={{textTransform:"lowercase"}}>
            <Icon name="up arrow" /> 
            {(graphData[graphData.length-1].weightma - graphData[0].weightma).toFixed(2)} lbs
        </Statistic.Label>  );
    }

    //We can put this into a useEffect because it is used for initialisation and retrieves from outside of React
    function handleFilterClick(interval) {
        var workingData = allData;
        var currentDate = new Date();
        if(interval == '') {
            setGraphData(allData);
            setdateFilter(interval);
          return true;
        }
        var fwaDate = new Date().setDate(currentDate.getDate() - interval);
        workingData = workingData.filter(
          (data) => {
            var dataDate = new Date(data.datetime);
            if(dataDate > fwaDate) { return true; } else { return false; }
          }
        );
        setGraphData(workingData);
        setdateFilter(interval);
    }

    useEffect(() => {
        async function fetchData() {
                    console.log("effect triggered");
                    try{
                        //Load in data
                        let data = await fetch(route('weightdash.query'), {
                            method: "GET",
                            cache: "no-cache", 
                            credentials: "same-origin",
                            headers: {
                            "Content-Type": "application/json", // request content type
                            "Accept": "application/json"
                            },
                            redirect: "follow",
                            referrerPolicy: "no-referrer",
                        });
                        data = await data.json();
                        const response = data;
                        setLoading(false);
                        setAllData(response);
                        setGraphData(response);
                        //handleFilterClick(dateFilter);
                        console.log("stateupdated");
                        if(dateFilter == '') {
                            setGraphData(response);
                            setdateFilter('');
                          return true;
                        }
                        var currentDate = new Date();
                        var fwaDate = new Date().setDate(currentDate.getDate() - dateFilter);
                        var workingData = response.filter(
                          (data) => {
                            var dataDate = new Date(data.datetime);
                            if(dataDate > fwaDate) { return true; } else { return false; }
                          }
                        );
                        setGraphData(workingData);
                    }catch(e) {
                        setError(e);
                        setLoading(false)
                    }
                }
                fetchData();
            }, 
            []
        );

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Weight Dashboard</h2>}
        >   
            <Head title={"Weight Dashboard"} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div padded="true" className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <Segment>

                            <Grid verticalAlign='middle' columns={2} stackable>
                                <Grid.Column width={12}>
                                    <GraphView graphData={graphData} dateFilter={dateFilter} loading={loading} error={error} handleClick={handleFilterClick} />
                                </Grid.Column>
                                <Grid.Column width={4}>
                                    <Segment>
                                        <Statistic>
                                            <Statistic.Value>{(typeof graphData[graphData.length - 1] != "undefined") ? graphData[graphData.length - 1].weightma : ""} <span style={{textTransform:"lowercase", fontSize: "0.5em"}}>lbs</span></Statistic.Value>
                                            {statistic}
                                        </Statistic>
                                    </Segment>
                                </Grid.Column>
                            </Grid>
                        </Segment>
                    </div>
                </div>
            </div>
            
        </AuthenticatedLayout>
    );
}
