import * as React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { closeGoalModal } from '../services/slices/goalModalSlice';
import { openGoalFormModal } from '../services/slices/goalFormModalSlice';
import Button from '../ui/Button';
import Card from '../ui/Card';
import { Icon } from '../utils/Icon';
import { goal } from '../utils/constants';


const GoalModal = () => {
    const {entry, entryType} = useSelector((state) => state.goalModal);
    const [selectedEntry, setSelectedEntry] = React.useState(entry);
    const [selectedGoal, setSelectedGoal] = React.useState(entryType);
    const dispatch = useDispatch();
    
    const getEntries = () => {
        return goal?.map((item) => ({
            id: item.id,
            title: item.title,
        }));
    }

    const getTypes = (type) => {
        const goalTypes = goal?.find((item) => item.title === type);
        return goalTypes?.types;
    }


    // close modal
    const close = () => dispatch(closeGoalModal());


    // continue
    const handleContinue = () => {
        dispatch(openGoalFormModal({
            entry: selectedEntry,
            entryType: selectedGoal,
        }));
        close();
    }

    return(
           <div className="cnx_ins__goal_modal__container">
                <Card className="cnx_ins__goal_modal__card">
                    <Card.Header 
                        className="cnx_ins__goal_modal__card_header"
                        onClose={close}
                    >
                        <div className='cnx_ins__goal_modal__card_header_title'>
                            Add Modal 1/2
                        </div>
                    </Card.Header>
                    {/* card body */}
                    <Card.Body className='cnx_ins__goal_modal'>
                            <div className='cnx_ins__goal_modal_entry hr'>
                                <div className='cnx_ins__goal_modal_entry_title'>CHOOSE ENTITY</div>
                                <div className='cnx_ins__goal_modal_entry_list'>
                                    {
                                        getEntries().map((item) => (
                                            <div 
                                                key={item.id} 
                                                onClick={() => {
                                                    setSelectedEntry(item.title);
                                                    setSelectedGoal('');
                                                }}
                                                className={`cnx_ins__goal_modal_entry_list_item ${selectedEntry === item.title ? 'active' :''}`
                                            }>
                                                <Icon type={item.title} />
                                                <span>{item.title}</span>
                                                {selectedEntry === item.title &&  <i className="fa-solid fa-chevron-right"></i>} 
                                            </div>
                                        ))
                                    }
                                </div>
                            </div>

                            <div className='cnx_ins__goal_modal_entry'>
                                <div className='cnx_ins__goal_modal_entry_choose'>
                                    <div className='cnx_ins__goal_modal_entry_title'>CHOOSE GOAL</div>
                                    <div className='cnx_ins__goal_modal_entry_list'>
                                        {
                                            getTypes(selectedEntry)?.map((item) => (
                                                <div 
                                                    key={item.id} 
                                                    onClick={() => setSelectedGoal(item.title)}
                                                    className={`cnx_ins__goal_modal_entry_list_item ${selectedGoal === item.title ? 'active' :''}`
                                                }>
                                                    {Icon(item.title)}
                                                    <div>
                                                        <span>{item.title}</span>
                                                        <article>
                                                            {item.subtitle}
                                                        </article>
                                                    </div>
                                                    {selectedGoal === item.title && <i className="fa-solid fa-check"></i>}
                                                </div>
                                            ))
                                        }
                                    </div>
                                </div>
                            </div>
                    </Card.Body>
                    {/* end card body */}
                    <Card.Footer>
                        <div className='cnx_ins__goal_modal__card_footer'>
                            <Button
                                onClick={close}
                                className='cnx_ins__goal_modal__card_footer_cancel'
                                variant='tertiary'
                            >Cancel</Button>
                            <Button onClick={handleContinue} disabled={ !selectedGoal } variant='success'>Continue</Button>
                        </div>
                    </Card.Footer>
                </Card>
           </div> 
  
    )
}


export default GoalModal;



