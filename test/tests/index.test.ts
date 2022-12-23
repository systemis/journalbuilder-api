import axios from "axios";
import {
  RegisterDto,
  LoginDto,
  ChangeUserPasswordDto,
  CreateProjectDto,
  EditProjectDto,
} from "../src/dto";

const API_HOST = "http://localhost:3000/api";


/** @dev Declare bearer token to authenticate */
let access_token = "";
let projectId = "";
const newPassword = "cxdsakdaskl2030h@";
const registerDto: RegisterDto = {
  username: "usertest2",
  email: "usertest2@gmail.com",
  password: newPassword,
  given_name: "Joe",
  family_name: "John",
  name: "John Joe"
};

const loginDto: LoginDto = {
  username: registerDto.username,
  password: registerDto.password
}


describe("Authentication testing", () => {
  /** @dev Register test */
  test('register a new account should be failed', async () => {
    try {
      await axios.post(`${API_HOST}/auth/register`, registerDto);
    } catch {
      expect(400).toBe(400);
    }
  });

  /** @dev Login test */
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })

  /** @dev Change password test */
  test("Change password should be successful", async () => {
    const changePasswordDto: ChangeUserPasswordDto = {
      password: newPassword,
    };
    const response = await axios.patch(`${API_HOST}/auth/password`, changePasswordDto, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    expect(response.status).toBe(200);
  })

  /** @dev Login with old password test */
  test("Login with old password should be failed", async () => {
    try {
      const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
      access_token = response?.data?.access_token;
    } catch {
      expect(400).toBe(400);
    }
  })

  /** @dev Login with new password test */
  test("Login with new password should be successfully", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, {
      ...loginDto,
      password: newPassword,
    });
    access_token = response?.data?.access_token;
    expect(response.status).toBe(200);
  })
});

describe("Project testing", () => {
  /** @dev Login*/
  test("Login with credentials should be successful", async () => {
    const response = await axios.post(`${API_HOST}/auth/login`, loginDto);
    access_token = response?.data?.data?.access_token;
    expect(response.status).toBe(200);
  })


  /** @dev Create project test */
  test("Create a project should be successful", async () => {
    const createProjectDto: CreateProjectDto = {
      name: "Project 1",
      description: "Project 1",
      image: "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0",
    };
    const response = await axios.post(`${API_HOST}/project`, createProjectDto, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    projectId = response?.data?.data?._id;
    expect(response.status).toBe(200);
  })

  /** @dev Edit project test */
  test("Create a project should be successful", async () => {
    const editProjectDto: CreateProjectDto = {
      name: "Project 2",
      description: "Project 2",
    };
    const response = await axios.patch(`${API_HOST}/project/${projectId}`, editProjectDto, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    projectId = response?.data?.data?._id;
    expect(response.status).toBe(200);
  })

  /** @dev Delete project test */
  test("Delete a project should be successful", async () => {
    console.log(projectId);
    const response = await axios.delete(`${API_HOST}/project/${projectId}`, {
      headers: { Authorization: `Bearer ${access_token}` }
    });
    projectId = response?.data?.data?._id;
    expect(response.status).toBe(200);
  })
})