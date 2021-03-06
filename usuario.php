<?php

defined ('BASEPATH') OR exit ('Ação não permitida!');

class Usuarios extends CI_Controller{
    
    public function __construct() {
        parent::__construct();
        
        //Definir se há sessão
    }
    
    public function index() {
        
        $data = array(
            
            'titulo' => 'Usuários cadastrados',
            
            
            'styles' =>array(
                'vendor/datatables/dataTables.bootstrap4.min.css',
            ),
            'scripts' => array(
                'vendor/datatables/jquery.dataTables.min.js',
                'vendor/datatables/dataTables.bootstrap4.min.js',
                'vendor/datatables/app.js',
            ),
            'usuarios' => $this->ion_auth->users()->result(),
            );
            
 //       echo '<pre>';
 //       print_r($data['usuarios']);
 //       exit();
        
        $this->load->view('layout/header', $data);
        $this->load->view('usuarios/index');
        $this->load->view('layout/footer');
    }
    
    public function edit ($usuario_id = NULL){
    
        if(!$usuario_id || !$this->ion_auth->user($usuario_id)->row()){
            
            //exit('Usuário não encontrado');
            
            $this->session->set_flashdata('error', 'Usuário não encontrado.');
            redirect ('usuarios');
        }else{
          
                
          /*
          *
              [first_name] => Admin
    [last_name] => istrator
    [email] => admin@admin.com
    [username] => administrator
    [active] => 1
    [perfil_usuario] => 1
    [password] => 
    [confirm_password] => 
    [usuario_id] => 1
    */
 //         echo '<pre>';
//        print_r($this->input->post());
 //         exit();
          

            
          $this->form_validation->set_rules('first_name','','trim|required');
          $this->form_validation->set_rules('last_name','','trim|required');
          $this->form_validation->set_rules('email','','trim|required|valid_email|callback_email_check');
          $this->form_validation->set_rules('username','','trim|required|callback_username_check');
          $this->form_validation->set_rules('password','senha','min_length[5]|max_length[255]');
          $this->form_validation->set_rules('confirm_password','confirme','matches[password]');
          
        if($this->form_validation->run()){
           
                $data = elements(
                        
                        array(
                           'fist_name',
                            'las_name',
                            'email',
                            'username',
                            'active',
                            'password',
                                ), $this->input->post()
                                                
                        );
                
                        $data = $this->security->xss_clean($data);
                        
                        
                        /*VERIFICAÇÃO DE SENHA PARA O DB*/
                        $password= $this->input->post('password');
                        
                        if(!$password){
                            
                            unset($data['password']);
                            
                        }
                        
                        $this->core_model->update('users', $data, array('id'=> $usuario_id));
                        
                        
                        
                        
                        //'perfil_usuario', => 1 /// Verificar
                        
                        $perfil_usuario_db = $this->ion_auth->get_users_groups($usuario_id)->row();
                        
                        $perfil_usuario_post = $this->input->post('perfil_usuario');
                       
                        
                        /*se for diferente, atualiza o grupo*/
                        if($perfil_usuario_post != $perfil_usuario_db->id){
                            
                            
                            $this->ion_auth->remove_from_group($perfil_usuario_db->id, $usuario_id);
                             $this->ion_auth->add_to_group($perfil_usuario_post, $usuario_id);
                        }
                        
                        $this->session->set_flashdata('sucesso', 'Dados salvos com sucesso.');
                    }else{
                        $this->session->set_flashdata('error', 'Erro ao salvar os dados.');
                    }
                    redirect('usuarios');
            
       }else{
           
        
          $data = array(
          'titulo' => 'Editar usuário',
          'usuario' => $this->ion_auth->user($usuario_id)->row(),
          'perfil_usuario' => $this->ion_auth->get_users_groups($usuario_id)->row(),
        );  
           
        }
          
        $this->load->view('layout/header', $data);
        $this->load->view('usuarios/edit');
        $this->load->view('layout/footer');
        }
    }
    
    public function email_check($email) {
        
        $usuario_id = $this->input->post('usuario_id');
        
        if($this->core_model->get_by_id('users', array('email' => $email, 'id !=' => $usuario_id))){
            
            $this->form_validation->set_message('email-check', 'Esse e-mail já existe.');
            
            return FALSE;
            
        }else{
            
            return TRUE;
            
        }
        
    }
    
    public function username_check($username) {
        
        $usuario_id = $this->input->post('usuario_id');
        
        if($this->core_model->get_by_id('users', array('username' => $username, 'id !=' => $usuario_id))){
            
            $this->form_validation->set_message('username_check', 'Esse usuario ja existe.');
            
            return FALSE;
            
        }else{
            
            return TRUE;
            
        }
        
    }
}
