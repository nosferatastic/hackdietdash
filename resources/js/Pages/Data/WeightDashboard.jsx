import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import GraphView from '@/Components/GraphView';
import { Head } from '@inertiajs/react';
import { Button, Segment, Table, Menu, Icon, Grid, Statistic } from 'semantic-ui-react';

  import { ComposedChart, Scatter, Line, CartesianGrid, XAxis, YAxis, ResponsiveContainer, ReferenceLine, Tooltip, Legend } from 'recharts';


  var prevweight, diff = 0;
  function epoch (date) {
    return Date.parse(date)
  }

export default function WeightDashboard({ auth, weightData }) {

    let result = [];
    weightData.map((dataPoint) => {
        var weightStore;
        if(dataPoint.interpolated) {
            weightStore = null;
        } else {
            weightStore = dataPoint.weightlbs;
        }
        var obj = {weightlbs: weightStore, weightma: dataPoint.weightma, datetime: epoch(new Date(dataPoint.datetime))};
    result.push(obj);
   });
 


   if((weightData[weightData.length-1].weightma - weightData[0].weightma).toFixed(2) < 0) {
    var statistic =  (<Statistic.Label style={{textTransform:"lowercase"}}>
        <Icon name="down arrow" /> 
        {(-1*(weightData[weightData.length-1].weightma - weightData[0].weightma)).toFixed(2)} lbs
    </Statistic.Label>  );
    } else {
        var statistic =  (<Statistic.Label style={{textTransform:"lowercase"}}>
            <Icon name="up arrow" /> 
            {(weightData[weightData.length-1].weightma - weightData[0].weightma).toFixed(2)} lbs
        </Statistic.Label>  );
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Weight Dashboard</h2>}
        >   
            <Head title="Weight Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div padded="true" className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <Segment>

                            <Grid verticalAlign='middle' columns={2} stackable>
                                <Grid.Column width={12}>
                                    <GraphView />
                                </Grid.Column>
                                <Grid.Column width={4}>
                                    <Segment>
                                    <Statistic size="small" style={{display: "flex",alignItems: "center",justifyContent: "center",height: "100%"}}>
                                        <Statistic.Value>{weightData[weightData.length - 1].weightma} <span style={{textTransform:"lowercase", fontSize: "0.5em"}}>lbs</span></Statistic.Value>
                                        {statistic}
                                        </Statistic>
                                    </Segment>
                                </Grid.Column>
                            </Grid>
                            <Table celled>
                                <Table.Header>
                                    <Table.Row>
                                    <Table.HeaderCell>Date</Table.HeaderCell>
                                    <Table.HeaderCell>Weight (lbs)</Table.HeaderCell>
                                    <Table.HeaderCell>WMA (lbs)</Table.HeaderCell>
                                    <Table.HeaderCell>Trend</Table.HeaderCell>
                                    </Table.Row>
                                </Table.Header>
                        
                                <Table.Body>
                                {weightData.map((answer, keyval) => {     
                                    if(!answer.interpolated) {
                                        diff =  prevweight - answer.weightlbs;
                                        prevweight = answer.weightlbs;         
                                        // Return the element. Also pass key     
                                        return (
                                            <Table.Row key={keyval}>
                                            <Table.Cell>
                                                {new Date(answer.datetime).toDateString()}
                                            </Table.Cell>
                                            <Table.Cell>{answer.weightlbs} lbs</Table.Cell>
                                            <Table.Cell>{answer.weightma} lbs</Table.Cell>
                                            <Table.Cell>{diff.toFixed(2)}</Table.Cell>
                                            </Table.Row>);
                                        }}) } 
                                </Table.Body>
                            </Table>
                        </Segment>
                    </div>
                </div>
            </div>
            
        </AuthenticatedLayout>
    );
}
