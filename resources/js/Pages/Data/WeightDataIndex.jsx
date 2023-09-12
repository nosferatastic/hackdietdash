import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import GraphView from '@/Components/GraphView';
import { Head } from '@inertiajs/react';
import { Segment, Table, Menu, Icon, Statistic } from 'semantic-ui-react';
import { Button, CircularProgress } from '@mui/material';
import Grid from '@mui/material/Unstable_Grid2'; // Grid version 2

import React from 'react';



  class EditableField extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            isWritable: false,
            currentValue: props.data ? props.data.weightlbs : 0,
            savedValue: props.data ? props.data.weightlbs : 0,
            date: props.date,
            loading: false,
            updateData: props.onSave
        }
        this.onEdit = this.onEdit.bind(this);
        this.onEditSaveClick = this.onEditSaveClick.bind(this);
        this.onCancelClick = this.onCancelClick.bind(this);
    }

    onEdit(e) {
        this.setState( {
            currentValue : e.target.value
        });
    }

    onEditSaveClick(e) {
        if(this.state.isWritable) { 
            //This is where we do the ajax call TODO
            this.setState({loading: true});
            var container = {
                date:this.state.date,
                weightlbs: this.state.currentValue
            }
            console.log(container);
            //Perform PUT call
            //If success, update savedValue in this and pass returned value up the chain to update parent state (maybe we don't need to update savedValue if it does go up chain)
            //If error, return to original savedValue and forget it ever happened
            window.setTimeout(() => {
                this.setState({loading:false, isWritable: !this.state.isWritable, savedValue: this.state.currentValue});
                //this.state.updateData(updated);
            }, 2000);
        } else {
            this.setState({isWritable: !this.state.isWritable, loading:false});
        }
    }

    onCancelClick(e) {
        //Overwrite state to savedValue and non-editable
        this.setState({currentValue: this.state.savedValue, isWritable: !this.state.isWritable});
    }

    render() {
        const {currentValue, savedValue, loading, isWritable} = this.state;
        return (
            <>
            <Grid container>
                <Grid xs={8}>
                    {/* Editable value field */}
                    {
                        this.state.isWritable &&
                        <Editable
                            value={currentValue != 0 ? currentValue : ""}
                            onFieldChange={this.onEdit}
                            disabled={this.state.loading}
                            name="field"
                            type="number"
                        />
                    }
                    {/* Text saved value */}
                    {!this.state.isWritable && <div style={{width:"100%", height:"100%", vAlign:"center", paddingTop:"0.5em"}}>{savedValue ? savedValue : "Not Set"}</div>}
                    </Grid>
                <Grid xs={4}>
                    {/* Combined Save/Edit Button */}
                    <Button onClick={this.onEditSaveClick}
                    disabled={loading}
                    variant="contained"
                    >
                        {isWritable ? "Save" : "Edit"}
                    {loading && (
                        <CircularProgress
                            size={24}
                            sx={{
                            color: "green",
                            position: 'absolute',
                            top: '50%',
                            left: '50%',
                            marginTop: '-12px',
                            marginLeft: '-12px',
                            }}
                        />
                    )}
                    </Button>
                    {/* Cancel Button */}
                    {isWritable &&
                    <Button onClick={this.onCancelClick}
                    disabled={loading}
                    variant="contained"
                    color="error"
                    >Cancel</Button>
                    }
                </Grid>
            </Grid>
            </>
        )

    }
  }

  const Editable = ({label, value, onFieldChange, type}) => (
        <input name="field" type={type} value={value} onChange={onFieldChange}/>
    )



export default function WeightDataIndex({ auth, weightData, dates }) {
    //Store page variable for pagination
    const [page, changePage] = React.useState({page: 1});
    //Data state separated from passed weight data on load so we can update it based on returned API calls. More agile.
    const [dataState, setDataState] = React.useState(weightData);

    /*
    * Function for updating local weight data store (only to be done after positive API call returned from create/update weight log call)
    */
    const updateWeightData = (updated) => {
        //New obj with updated state
        const newData = dataState.map((data) => {
            return data.date == updated.date ? updated : data
        });
        //Still need to handle what if it's a new one?

        setDataState(newData);
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
                            <Table celled>
                                <Table.Header>
                                    <Table.Row>
                                    <Table.HeaderCell>Date</Table.HeaderCell>
                                    <Table.HeaderCell width={5}>Weight (lbs)</Table.HeaderCell>
                                    </Table.Row>
                                </Table.Header>
                        
                                <Table.Body>
                                { 
                                    dates.map((date, keyval) => {
                                        var displayDate = new Date(date).toLocaleDateString();
                                        return (<Table.Row key={keyval}>
                                        <Table.Cell>{displayDate}</Table.Cell>
                                        <Table.Cell>
                                            <EditableField date={date} label="Weight" data={dataState[date]} onSave={updateWeightData} />
                                        </Table.Cell>
                                        </Table.Row>);
                                    })
                                }
                                </Table.Body>
                            </Table>
                        </Segment>
                    </div>
                </div>
            </div>
            
        </AuthenticatedLayout>
    );
}
