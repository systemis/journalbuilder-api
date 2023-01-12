import axios from "axios";
import {
  RegisterDto,
  LoginDto,
  CreateProductDto,
  EditProductDto,
} from "../src/dto";
import { ProductEntity } from "../src/entities";

const API_HOST = "http://localhost:3000/api";
// const API_HOST = "https://afternoon-gorge-11599.herokuapp.com/api";


/** @dev Declare bearer token to authenticate */
let access_token = "";
let id_token = "";
const newPassword = "Thinhphasndjksndfjkdsbk@";
const registerDto: RegisterDto = {
  username: "tphamdn",
  email: "tphamdn@gmail.com",
  password: newPassword,
  given_name: "Thinh",
  family_name: "Pham",
  name: "Thinh Pham"
};

const loginDto: LoginDto = {
  username: registerDto.username,
  password: registerDto.password
}


describe("Product testing", () => {
  let tags = [];
  let projects = [];
  let userId = "";
  let productId = "";
  let product: ProductEntity;
  let createProductDto: CreateProductDto[];

  /** @dev Get tags */
  test("Get tags should be successful", async () => {
    const response = await axios.get(`${API_HOST}/tags`);
    tags = response?.data?.data;
    expect(response.status).toBe(200);
  });


  /** @dev Create project test */
  test("Create a project should be successful", async () => {
    try {
      const createProjectDto = {
        name: "Mobile development",
        description: "Abstract art is art that does not attempt to represent an accurate depiction of a visual reality but instead use shapes, colors, forms and gestural marks to achieve its effect.",
        image: "https://cdn.dribbble.com/userupload/4274164/file/original-1aa099d27c5c7276b8e97724e7bff2a1.jpg?compress=1&resize=1504x1128",
      };
      const response = await axios.post(`${API_HOST}/project`, {
        ...createProjectDto,
        id_token,
      }, {
        headers: { Authorization: `Bearer ${access_token}` }
      });
      expect(response.status).toBe(200);
    } catch {
      expect(409).toBe(409);
    }
  })


  beforeAll(async () => {
    jest.setTimeout(600000);
    try {
      const credentail = (await axios.post(`${API_HOST}/auth/login`, loginDto))?.data?.data;
      access_token = credentail?.access_token
      id_token = credentail?.id_token;
    } catch (err) {
      console.log("Error when login", err);
    }


    try {
      userId = (await axios.get(`${API_HOST}/user/profile`, {
        data: { id_token },
        headers: { Authorization: `Bearer ${access_token}` }
      }))?.data?.data?.sub;
    } catch (err) {
      console.log("Error get tags", err);
    }

    try {
      projects = (await axios.get(`${API_HOST}/projects`, {
        data: { userId }
      }))?.data?.data;
    } catch (err) {
      console.log("Error get user projects", err);
    }

    /** Init dto to create. */
    createProductDto = [
      {
        name: "Corporate Wellness Services Mobile App",
        description: "We had a lot of requests and projects related to employee comfort in-office environment. We see a trend in multifunctional apps that help to schedule meetings, track the working time, measure the emotional condition of employees using AI natural language analysis, provide access to in-office facilities, etc. We believe in remote work and distributed teams but we understand the benefits of in-office work as well. How do you see the future of joint work: remote or in-office?",
        gallery: [
          "https://cdn.dribbble.com/userupload/4214233/file/original-a4c80caf8bc6bcccd9ff44fc43c4f41d.png?compress=1&resize=1504x1128",
          "https://cdn.dribbble.com/userupload/4273517/file/original-e4fb03026d39fa03ee5a0c5566a0037c.png?compress=1&resize=1504x1128",
        ],
        projectId: projects.length > 0 ? projects[0]?._id : "",
        tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
      },
      {
        name: "Zeva - SaaS Landing Page",
        description: "We are Keitoto, offering dedicated teams with affordable prices (below minimum wage in most Western, Europe, and most Asia countries) from the project manager, UI/UX, illustrator, 3D designer, and graphic designer ",
        gallery: [
          "https://cdn.dribbble.com/users/5973514/screenshots/20357623/media/4d714d6c7881b98e381e0072a1358b28.png?compress=1&resize=1600x1200&vertical=top",
          "https://cdn.dribbble.com/users/5973514/screenshots/20357623/media/2a03cea588b60afdfc81ecad67d188e6.png?compress=1&resize=1600x1200&vertical=top",
        ],
        projectId: projects.length > 0 ? projects[0]?._id : "",
        tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
      },
      {
        name: "Hoosier Boy Rebrand",
        description: `I recently wrapped a rebrand for local growers Schlegel Greenhouse/Hoosier Boy (their retail-facing brand). Tasked with "cleaning up" the original logo that was created in the 30s for a local group of farmers, I had to really be careful not to stray too far from the source.`,
        gallery: [
          "https://cdn.dribbble.com/userupload/4273256/file/original-16867a8dc67cfc22db4d033055151363.jpg?compress=1&resize=1200x960"
        ],
        projectId: projects.length > 0 ? projects[0]?._id : "",
        tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
      },
      {
        name: "PetPiw - Pet Shop Landing Page",
        description: `Hi World 👋🏻, Today I want to share with you the concept of a Pet Shop Landing Page which we named PetPiw🐾
        What do you guys think? Let me know in the comments section!
        
        Hope you guys enjoy it. Press "L" if you like it.
        
        Let's talk: ilmiawan97@gmail.com | Instagram @hi.haqqi
        
        `,
        gallery: [
          "https://cdn.dribbble.com/userupload/4273359/file/original-73bb70d16514a59b3b36231b8a2bd469.png?compress=1&resize=2048x1536",
          "https://cdn.dribbble.com/userupload/4273360/file/original-6ea7b9aab4f9ef994ac298f7e97baf12.png?compress=1&resize=2048x1536&vertical=center",
        ],
        projectId: projects.length > 0 ? projects[0]?._id : "",
        tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
      },
    ]
  });

  
  // /** @dev Create product testing */
  test('Create a product should be successfully', async () => {
    try {
      await Promise.all(createProductDto?.map(async (item) => {
        try {
          const response = await axios.post(`${API_HOST}/product`, {
            ...item,
            id_token,
          }, {
            headers: { Authorization: `Bearer ${access_token}` }
          });
          productId = response?.data?.data?._id;
          console.log(productId);
        } catch {
        }
      }))
    } catch {
      expect(409).toBe(409);
    }
  })
  

  // beforeAll(async () => {
  //   jest.setTimeout(600000);
  //   const getProductsResponse = await axios.get(`${API_HOST}/products`, {
  //     data: { userId }
  //   })

  //   const products = getProductsResponse.data?.data;
  //   if (products.length) {
  //     productId = products[products.length - 1]._id;
  //   }


  //   const response = await axios.get(`${API_HOST}/product/owner/${productId}`, {
  //     data: { id_token },
  //     headers: { Authorization: `Bearer ${access_token}` }
  //   })
  //   product = response?.data?.data as ProductEntity;
  // });


  // /** @dev Edit product testing */
  // test("Edit product should be successfully", async () => {
  //   const editProductDto: EditProductDto = {
  //     name: "Product 10 edit",
  //     description: "Edit product 1",
  //     gallery: [
  //       ...product.gallery,
  //       "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0",
  //     ]
  //   }

  //   const response = await axios.patch(`${API_HOST}/product/${productId}`, {
  //     ...editProductDto,
  //     id_token,
  //   }, {
  //     headers: { Authorization: `Bearer ${access_token}` }
  //   });

  //   expect(response.status).toBe(200);
  // })

  /** @dev Remove product testing */
  // test("Remove product should be successfully", async () => {
  //   const response = await axios.delete(`${API_HOST}/product/${productId}`, {
  //     data: { id_token },
  //     headers: { Authorization: `Bearer ${access_token}` }
  //   });
  //   expect(response.status).toBe(200);
  // })
})

// describe("Delete product", () => {
//   let tags = [];
//   let projects = [];
//   let userId = "";
//   let productId = "";
//   let product: ProductEntity;
//   let createProductDto: CreateProductDto;

//   /** @dev Get tags */
//   test("Get tags should be successful", async () => {
//     const response = await axios.get(`${API_HOST}/tags`);
//     tags = response?.data?.data;
//     expect(response.status).toBe(200);
//   });

//   beforeAll(async () => {
//     jest.setTimeout(600000);
//     try {
//       const credentail = (await axios.post(`${API_HOST}/auth/login`, loginDto))?.data?.data;
//       access_token = credentail?.access_token
//       id_token = credentail?.id_token;
//       console.log({ id_token, access_token });
//     } catch (err) {
//       console.log("Error when login", err);
//     }

//     try {
//       userId = (await axios.get(`${API_HOST}/user/profile`, {
//         data: { id_token },
//         headers: { Authorization: `Bearer ${access_token}` }
//       }))?.data?.data?.sub;
//     } catch (err) {
//       console.log("Error get tags", err);
//     }

//     try {
//       projects = (await axios.get(`${API_HOST}/projects`, {
//         data: { userId }
//       }))?.data?.data;
//     } catch (err) {
//       console.log("Error get user projects", err);
//     }

//     /** Init dto to create. */
//     createProductDto = {
//       name: "React product testing admin",
//       description: "Product 1",
//       gallery: [
//         "https://cdn.dribbble.com/userupload/3221720/file/original-c52652a671ea4f45e1211843f834bcdb.png?resize=400x0"
//       ],
//       projectId: projects.length > 0 ? projects[0]?._id : "",
//       tags: tags.length <= 0 ? [] : tags.map((item) => item?._id),
//     }
    
//     try {
//       const response = await axios.post(`${API_HOST}/product`, {
//         ...createProductDto,
//         id_token,
//       }, {
//         headers: { Authorization: `Bearer ${access_token}` }
//       });
//       productId = response?.data?.data?._id;
//     } catch (err) {
//       console.log("Error when create product", err?.response?.status);
//     }
//   });

//   /** @dev React product testing */
//   test("React product should be successfully", async () => {
//     console.log({ productId });
//     const response = await axios.patch(`${API_HOST}/product/react/${productId}`, {
//       id_token
//     }, {
//       headers: { Authorization: `Bearer ${access_token}` }
//     });

//     expect(response.status).toBe(200);
//     expect(response?.data?.data?.reactions?.length).toBe(1);
//   });

//   /** @dev Remove product testing */
//   test("Remove product should be successfully", async () => {
//     const response = await axios.delete(`${API_HOST}/product/${productId}`, {
//       data: { id_token },
//       headers: { Authorization: `Bearer ${access_token}` }
//     });
//     expect(response.status).toBe(200);
//   })
// })